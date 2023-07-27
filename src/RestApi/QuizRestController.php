<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Quiz\QuizGateway;
use Foodsharing\Modules\Quiz\QuizSessionGateway;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use OpenApi\Annotations as OA;
use FOS\RestBundle\Controller\Annotations as Rest;

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
     * @Rest\Post("quiz/{quizId}/start", requirements={"quizId" = "\d+"})
     */
    public function startQuizSession(int $quizId): Response
    {
        $this->sanityChecks($quizId);
        $this->assertSessionRunning($quizId, false);

        $quiz = $this->quizGateway->getQuiz($quizId);

        // TODO get easymode
        $easymode = true;
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
        $question['answers'] = $this->quizGateway->getAnswers($question['id'], false);
        
        return $this->handleView($this->view([
            'question' => $question,
        ], 200));
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