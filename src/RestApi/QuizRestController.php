<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Quiz\AnswerRating;
use Foodsharing\Modules\Core\DBConstants\Quiz\QuizStatus;
use Foodsharing\Modules\Quiz\QuizGateway;
use Foodsharing\Modules\Quiz\QuizSessionGateway;
use Foodsharing\Modules\WallPost\WallPostGateway;
use Foodsharing\RestApi\Models\Quiz\QuizAnswerModel;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

// TODO: permission checks

final class QuizRestController extends AbstractFOSRestController
{
    public const NETWORK_BUFFER_TIME_IN_SECONDS = 5;

    public function __construct(
        private Session $session,
        private QuizGateway $quizGateway,
        private QuizSessionGateway $quizSessionGateway,
        private WallPostGateway $wallPostGateway,
    ) {
    }

    /**
     * @OA\Tag(name="quiz")
     * @Rest\RequestParam(name="timed", default=true)
     * @Rest\Post("quiz/{quizId}/start", requirements={"quizId" = "\d+"})
     */
    public function startQuizSession(int $quizId, ParamFetcher $paramFetcher): Response
    {
        $this->quizSanityChecks($quizId);
        $this->assertSessionRunning($quizId, false);

        $quiz = $this->quizGateway->getQuiz($quizId);

        $easymode = !$paramFetcher->get('timed');
        if ($easymode && $quizId != Role::FOODSAVER) {
            throw new BadRequestHttpException('Easymode is only allowed on the Foodsaver Quiz.');
        }

        if ($quizId == Role::FOODSAVER && $easymode) {
            $quiz['questcount'] *= 2;
        }
        // TODO: Make sure the user is allowed to do this quiz!

        $questions = $this->quizGateway->getFairQuestions($quiz['questcount'], $quizId);
        $this->quizSessionGateway->initQuizSession($this->session->id(), $quizId, $questions, $quiz['maxfp'], $easymode);

        return $this->handleView($this->view(['questions' => count($questions), 'timed' => !$easymode], 200));
    }

    /**
     * @OA\Tag(name="quiz")
     * @Rest\Get("quiz/{quizId}/status", requirements={"quizId" = "\d+"})
     */
    public function getQuizStatus(int $quizId): Response
    {
        // TODO clean up with gatewaycount()
        $quiz = $this->quizSanityChecks($quizId);
        $session = $this->quizSessionGateway->getRunningSession($quizId, $this->session->id());
        $status = $this->quizSessionGateway->getQuizStatus($quizId, $this->session->id());
        $status['questions'] = $quiz['questcount'];
        $status['allowUntimed'] = $quizId == Role::FOODSAVER;
        if ($status['status'] == QuizStatus::RUNNING) {
            $status['questions'] = count($session['quiz_questions']);
            $status['answered'] = $session['quiz_index'];
            $status['timed'] = !$session['easymode'];
        }

        return $this->handleView($this->view($status, 200));
    }

    /**
     * @OA\Tag(name="quiz")
     * @Rest\Get("quiz/{quizId}/question", requirements={"quizId" = "\d+"})
     */
    public function getNextQuestion(int $quizId): Response
    {
        $this->quizSanityChecks($quizId);
        $session = $this->assertSessionRunning($quizId);
        $question_sessiondata = &$session['quiz_questions'][$session['quiz_index']];
        $question = $this->quizGateway->getQuestion($question_sessiondata['id']);

        if (isset($question_sessiondata['start_time'])) {
            // test for timeout:
            $question_age = time() - $question_sessiondata['start_time'];
            if (!$session['easymode'] && $question_age > $question_sessiondata['duration']) {
                //set question as timed out and try getting a question again
                $question_sessiondata['userduration'] = $question_age;
                $this->set_question_answered($quizId, $session);

                return $this->getNextQuestion($quizId);
            }
            $question['age'] = $question_age;
        } else {
            $question_sessiondata['start_time'] = time();
            $this->quizSessionGateway->updateQuizSession($session);
        }
        $question['answers'] = $this->quizGateway->getAnswers($question['id'], false);
        shuffle($question['answers']);
        $question['timed'] = !$session['easymode'];
        $question['index'] = $session['quiz_index'];

        return $this->handleView($this->view([
            'question' => $question,
        ], 200));
    }

    /**
     * @OA\Tag(name="quiz")
     * @Rest\Post("quiz/{quizId}/answer", requirements={"quizId" = "\d+"})
     * @OA\RequestBody(@Model(type=QuizAnswerModel::class))
     * @ParamConverter("answers", converter="fos_rest.request_body")
     */
    public function answerNextQuestion(int $quizId, QuizAnswerModel $answers): Response
    {
        $this->quizSanityChecks($quizId);
        $session = $this->assertSessionRunning($quizId);
        $question = &$session['quiz_questions'][$session['quiz_index']];

        //Check that only answers to the question were given
        $solution = $this->quizGateway->getAnswers($question['id']);
        foreach ($answers->answers as &$answer) {
            if (!in_array($answer, array_column($solution, 'id'))) {
                throw new BadRequestHttpException('Invalid answerId given.');
            }
        }

        //Update quiz state stored in session:
        $question['answers'] = $answers->answers;
        $question['userduration'] = (time() - $question['start_time']);
        $this->set_question_answered($quizId, $session);

        return $this->handleView($this->view([
            'answered' => $question['answers'],
            'solution' => $solution,
        ], 200));
    }

    private function set_question_answered(int $quizId, array $session)
    {
        ++$session['quiz_index'];
        $this->quizSessionGateway->updateQuizSession($session);

        if ($session['quiz_index'] == count($session['quiz_questions'])) {
            $this->finalizeQuiz($quizId, $session);
        }
    }

    /**
     * @OA\Tag(name="quiz")
     * @Rest\Get("quiz/{quizId}/results", requirements={"quizId" = "\d+"})
     */
    public function getQuizResults(int $quizId): Response
    {
        $this->quizSanityChecks($quizId);
        $this->assertSessionRunning($quizId, false);

        $session = $this->quizSessionGateway->getLatestSession($quizId, $this->session->id());
        if (!$session) {
            throw new AccessDeniedHttpException('There must be at least one finished quiz session.');
        }

        return $this->handleView($this->view($session, 200));
    }

    /**
     * @OA\Tag(name="quiz")
     * @Rest\RequestParam(name="text", nullable=false)
     * @Rest\Post("question/{questionId}/comment", requirements={"questionId" = "\d+"})
     */
    public function addCommentToQuestion(int $questionId, ParamFetcher $paramFetcher): Response
    {
        $question = $this->questionSanityChecks($questionId);

        $this->quizGateway->addUserComment($questionId, $this->session->id(), $paramFetcher->get('text'));

        return $this->handleView($this->view(['success' => true], 200));
    }

    private function finalizeQuiz(int $quizId, array $session)
    {
        $quiz = $this->quizGateway->getQuiz($quizId);
        $failurePointsTotal = 0;
        $quizLog = [];
        foreach ($session['quiz_questions'] as &$answered_question) {
            $failurePoints = 0;
            $question = $this->quizGateway->getQuestion($answered_question['id']);
            $solution = $this->quizGateway->getAnswers($answered_question['id']);
            $question['timed_out'] = !$session['easymode'] && ($answered_question['userduration'] > $answered_question['duration'] + self::NETWORK_BUFFER_TIME_IN_SECONDS);
            if ($question['timed_out']) {
                $failurePoints = $answered_question['fp'];
            } else {
                foreach ($solution as $answer) {
                    $answerWasSelected = in_array($answer['id'], $answered_question['answers']);
                    $answeredWrongly = ($answerWasSelected && $answer['right'] == AnswerRating::WRONG)
                        || (!$answerWasSelected && $answer['right'] == AnswerRating::CORRECT)
                        || ($answered_question['userduration'] > $answered_question['duration']);
                    $failurePoints += round($answeredWrongly * $question['fp'] / count($solution), 3);
                }
            }
            $failurePointsTotal += $failurePoints;
            $question['answers'] = $solution;
            $question['useranswers'] = $answered_question['answers'] ?? [];
            $question['userfp'] = $failurePoints;
            $question['userduration'] = $answered_question['userduration'];
            $quizLog[] = $question;
        }
        $this->quizSessionGateway->finishQuizSession($session['id'], $session['quiz_questions'], $quizLog, $failurePointsTotal, $quiz['maxfp']);
    }

    private function assertSessionRunning(int $quizId, bool $isSet = true)
    {
        $session = $this->quizSessionGateway->getRunningSession($quizId, $this->session->id());
        if (!$session && $isSet) {
            throw new AccessDeniedHttpException('There must be a running quiz session.');
        } if ($session && !$isSet) {
            throw new AccessDeniedHttpException('There must not be a running quiz session.');
        }

        return $session;
    }

    // Quiz editing funcitonality from here on:

    /**
     * @OA\Tag(name="quiz")
     * @Rest\Get("quiz/{quizId}", requirements={"quizId" = "\d+"})
     */
    public function getQuizDetails(int $quizId): Response
    {
        $quiz = $this->quizSanityChecks($quizId);

        return $this->handleView($this->view($quiz, 200));
    }

    /**
     * @OA\Tag(name="quiz")
     * @Rest\RequestParam(name="name", nullable=false)
     * @Rest\RequestParam(name="desc", nullable=false)
     * @Rest\RequestParam(name="maxfp", nullable=false)
     * @Rest\RequestParam(name="questcount", nullable=false)
     * @Rest\RequestParam(name="questcount_untimed", nullable=true)
     * @Rest\Patch("quiz/{quizId}", requirements={"questionId" = "\d+"})
     */
    public function updateQuiz(int $quizId, ParamFetcher $paramFetcher): Response
    {
        $this->quizSanityChecks($quizId);

        $this->quizGateway->updateQuiz(
            $quizId,
            $paramFetcher->get('name'),
            $paramFetcher->get('desc'),
            $paramFetcher->get('maxfp'),
            $paramFetcher->get('questcount'),
            $paramFetcher->get('questcount_untimed')
        );

        return $this->handleView($this->view(['success' => true], 200));
    }


    /**
     * @OA\Tag(name="quiz")
     * @Rest\RequestParam(name="text", nullable=false)
     * @Rest\RequestParam(name="fp", nullable=false)
     * @Rest\RequestParam(name="duration", nullable=false)
     * @Rest\RequestParam(name="wikilink", nullable=false)
     * @Rest\Patch("question/{questionId}", requirements={"questionId" = "\d+"})
     */
    public function updateQuestion(int $questionId, ParamFetcher $paramFetcher): Response
    {
        $this->questionSanityChecks($questionId);

        $this->quizGateway->updateQuestion(
            $questionId,
            $paramFetcher->get('text'),
            $paramFetcher->get('fp'),
            $paramFetcher->get('duration'),
            $paramFetcher->get('wikilink')
        );

        return $this->handleView($this->view(['success' => true], 200));
    }


    /**
     * @OA\Tag(name="quiz")
     * @Rest\Get("quiz/{quizId}/questions", requirements={"quizId" = "\d+"})
     */
    public function getAllQuestions(int $quizId): Response
    {
        $this->quizSanityChecks($quizId);
        $questions = $this->quizGateway->listQuestions($quizId);

        return $this->handleView($this->view($questions, 200));
    }

    /**
     * @OA\Tag(name="quiz")
     * @Rest\Get("question/{questionId}/comments", requirements={"questionId" = "\d+"})
     */
    public function getQuestionComments(int $questionId): Response
    {
        $question = $this->questionSanityChecks($questionId);
        $comments = $this->wallPostGateway->getPosts('question', $questionId);

        return $this->handleView($this->view($comments, 200));
    }

    private function quizSanityChecks(int $quizId)
    {
        if (!$this->session->id()) {
            throw new UnauthorizedHttpException('');
        }
        $quiz = $this->quizGateway->getQuiz($quizId);
        if (!$quiz) {
            throw new BadRequestHttpException('Invalid quizId given.');
        }

        return $quiz;
    }

    private function questionSanityChecks($questionId)
    {
        if (!$this->session->id()) {
            throw new UnauthorizedHttpException('');
        }
        $question = $this->quizGateway->getQuestion($questionId);
        if (!$question) {
            throw new BadRequestHttpException('Invalid questionId given.');
        }

        return $question;
    }
}
