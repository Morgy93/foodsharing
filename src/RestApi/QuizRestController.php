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
        if (!$this->session->id()) {
            throw new UnauthorizedHttpException('');
        }

        $runningQuizSession = $this->quizSessionGateway->getRunningSession($quizId, $this->session->id());
        if ($runningQuizSession) {
            throw new AccessDeniedHttpException('There is already a running quiz session.');
        }

        $quiz = $this->quizGateway->getQuiz($quizId);
        if(!$quiz){
            throw new BadRequestHttpException('Invalid quizId given.');
        }

        // TODO get easymode
        $easymode = true;
        if ($easymode && $quizId != Role::FOODSAVER) {
            throw new BadRequestHttpException('Easymode is only allowed on the Foodsaver Quiz.');
        }

        if ($quizId == Role::FOODSAVER && $easymode) {
            $quiz['questcount'] *= 2;
        }

        $questions = $this->quizGateway->getFairQuestions($quiz['questcount'], $quizId);


        
        return $this->handleView($this->view([
            'quiz' => $quiz,
            'questions' => $questions,
        ], 200));

        // TODO: Make sure the user is allowed to do this quiz!


        // $questions = $this->getRandomQuestions($quizId, $quiz['questcount']);
        // if ($questions) {
        //     // for safety check if there are not too many questions
        //     $questions = array_slice($questions, 0, (int)$quiz['questcount']);

        //     /*
        //         * Store quiz data in the users session
        //         */
        //     $this->session->set('quiz-id', $quizId);
        //     $this->session->set('quiz-questions', $questions);
        //     $this->session->set('quiz-index', 0);
        // }



        // try {
        //     $isConfirmed = $this->storeTransactions->joinPickup($storeId, $date, $fsId, $this->session->id());

        //     return $this->handleView($this->view([
        //             'isConfirmed' => $isConfirmed
        //         ], 200));
        // } catch (StoreTransactionException $ex) {
        //     throw new AccessDeniedHttpException($ex->getMessage(), $ex);
        // }
    }
}