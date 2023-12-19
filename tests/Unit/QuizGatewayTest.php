<?php

declare(strict_types=1);

namespace Tests\Unit;

use Codeception\Test\Unit;
use Foodsharing\Modules\Quiz\QuizGateway;
use Tests\Support\UnitTester;

class QuizGatewayTest extends Unit
{
    protected UnitTester $tester;
    private QuizGateway $gateway;
    private array $foodsaver;

    public function _before()
    {
        $this->gateway = $this->tester->get(QuizGateway::class);

        $this->foodsaver = $this->tester->createFoodsaver();

        foreach (range(1, 3) as $quizId) {
            $this->tester->createQuiz($quizId);
        }
    }

    public function testGetQuizzes(): void
    {
        $quizzes = $this->gateway->listQuiz();
        $this->assertEquals('1', $quizzes[0]['id']);
        $this->assertEquals('2', $quizzes[1]['id']);
        $this->assertEquals('3', $quizzes[2]['id']);
    }

    public function testAddQuestion(): void
    {
        $questionId = $this->gateway->addQuestion(1, 'question text', 3, 60);
        $this->tester->seeInDatabase('fs_question', ['text' => 'question text']);
        $this->tester->seeInDatabase('fs_question_has_quiz', ['question_id' => $questionId, 'quiz_id' => 1]);
    }

    public function testDeleteQuestion(): void
    {
        $this->tester->seeInDatabase('fs_question', ['id' => 1]);
        $this->tester->seeInDatabase('fs_question_has_quiz', ['quiz_id' => 1, 'question_id' => 1]);
        $this->tester->seeInDatabase('fs_answer', ['question_id' => 1]);

        $this->gateway->deleteQuestion(1);

        $this->tester->dontSeeInDatabase('fs_question', ['id' => 1]);
        $this->tester->dontSeeInDatabase('fs_question_has_quiz', ['question_id' => 1]);
        $this->tester->dontSeeInDatabase('fs_answer', ['question_id' => 1]);
    }
}
