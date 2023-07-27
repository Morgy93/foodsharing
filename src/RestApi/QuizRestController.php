<?php

namespace Foodsharing\RestApi;

use Carbon\Carbon;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Quiz\AnswerRating;
use Foodsharing\Modules\Quiz\QuizGateway;
use Foodsharing\Modules\Quiz\QuizSessionGateway;
use Foodsharing\RestApi\Models\Quiz\QuizAnswerModel;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use OpenApi\Annotations as OA;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class QuizRestController extends AbstractFOSRestController
{
    public function __construct(
        private Session $session,
        private QuizGateway $quizGateway,
        private QuizSessionGateway  $quizSessionGateway,
    ) {
    }

    /**
     * @OA\Tag(name="quiz")
     * @Rest\RequestParam(name="timed", default=true)
     * @Rest\Post("quiz/{quizId}/start", requirements={"quizId" = "\d+"})
     */
    public function startQuizSession(int $quizId, ParamFetcher $paramFetcher): Response
    {
        $this->sanityChecks($quizId);
        $this->assertSessionRunning($quizId, false);

        $quiz = $this->quizGateway->getQuiz($quizId);

        // TODO get easymode
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

        return $this->handleView($this->view(null, 200));
    }


    /**
     * @OA\Tag(name="quiz")
     * @Rest\Get("quiz/{quizId}/status", requirements={"quizId" = "\d+"})
     */
    public function getQuizStatus(int $quizId): Response
    {
        $this->sanityChecks($quizId);

        $session = $this->quizSessionGateway->getRunningSession($quizId, $this->session->id());
        if (!$session) {
            return $this->handleView($this->view(['running' => false], 200));
        }
        return $this->handleView($this->view([
            'running' => true,
            'questions' => count($session['quiz_questions']),
            'answered' => $session['quiz_index'],
            'timed' => !$session['easymode'],
        ], 200));
    }

    /**
     * @OA\Tag(name="quiz")
     * @Rest\Get("quiz/{quizId}/question", requirements={"quizId" = "\d+"})
     */
    public function getNextQuestion(int $quizId): Response
    {
        $this->sanityChecks($quizId);
        $session = $this->assertSessionRunning($quizId);
        $question = $this->quizGateway->getQuestion($session['quiz_questions'][$session['quiz_index']]['id']);
        $this->session->set('quiz-quest-start', time());

        $this->quizSessionGateway->updateQuizSession($session['id'], $session['quiz_questions'], $session['quiz_index']);
        $question['answers'] = $this->quizGateway->getAnswers($question['id'], false);
        
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
        $this->sanityChecks($quizId);
        $session = $this->assertSessionRunning($quizId);
        $question = $session['quiz_questions'][$session['quiz_index']];

        //Check that only answers to the question were given
        $solution = $this->quizGateway->getAnswers($question['id']);
        foreach ($answers->answers as &$answer) {
            if (!in_array($answer, array_column($solution, 'id'))) {
                throw new BadRequestHttpException('Invalid answerId given.');
            }
        }

        //Update quiz state stored in session:
        $question['answers'] = $answers->answers;
        $question['userduration'] = (time() - (int)$this->session->get('quiz-quest-start'));
        $session['quiz_questions'][$session['quiz_index']] = $question;
        $this->quizSessionGateway->updateQuizSession($session['id'], $session['quiz_questions'], $session['quiz_index'] + 1);

        if($session['quiz_index'] + 1 == count($session['quiz_questions'])) {
            $this->finalizeQuiz($quizId, $session);
        }

        return $this->handleView($this->view([
            'answered' => $answers->answers,
            'solution' => $solution
        ], 200));
    }

    private function finalizeQuiz(int $quizId, array $session) {
        $quiz = $this->quizGateway->getQuiz($quizId);
        $failurePointsTotal = 0;
        $quizLog = [];
        foreach ($session['quiz_questions'] as &$answered_question) {
            $failurePoints = 0;
            $question = $this->quizGateway->getQuestion($answered_question['id']);
            $solution = $this->quizGateway->getAnswers($answered_question['id']);
            foreach ($solution as $answer) {
                $answerWasSelected = in_array($answer['id'], $answered_question['answers']);
                $answeredWrongly = ($answerWasSelected && $answer['right'] == AnswerRating::WRONG) || (!$answerWasSelected && $answer['right'] == AnswerRating::CORRECT);
                $failurePoints += round($answeredWrongly * $question['fp'] / count($solution), 3);
            }
            $failurePointsTotal += $failurePoints;
            $question['answeres'] = $solution;
            $question['useranswers'] = $answered_question['answers'];
            $question['userfp'] = $failurePoints;
            $question['userduration'] = $answered_question['userduration'];
            $quizLog[] = $question;
        }
        $this->quizSessionGateway->finishQuizSession($session['id'], $session['quiz_questions'], $quizLog, $failurePointsTotal, $quiz['maxfp']);
    }

    private function sanityChecks(int $quizId) {
        if (!$this->session->id()) {
            throw new UnauthorizedHttpException('');
        }
        if(!$this->quizGateway->getQuiz($quizId)){
            throw new BadRequestHttpException('Invalid quizId given.');
        }
    }

    private function assertSessionRunning(int $quizId, bool $isSet = true) {
        $session = $this->quizSessionGateway->getRunningSession($quizId, $this->session->id());
        if (!$session && $isSet) {
            throw new AccessDeniedHttpException('There must be a running quiz session.');
        } if ($session && !$isSet) {
            throw new AccessDeniedHttpException('There must not be a running quiz session.');
        }
        return $session;
    }
}