<?php

namespace Foodsharing\Modules\QuizEditor;

use Foodsharing\Lib\FoodsharingController;
use Foodsharing\Modules\Quiz\QuizGateway;
use Foodsharing\Permissions\QuizPermissions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QuizEditorController extends FoodsharingController
{
    #[Route(path: '/quiz/edit/{quizId}', name: 'quiz.edit', requirements: ['storeId' => '\d+'], methods: ['GET'])]
    public function index(
        int $quizId,
        QuizGateway $quizGateway,
        QuizPermissions $quizPermissions,
        Request $request
    ): Response {
        if (!$this->session->mayRole()) {
            $this->routeHelper->goLoginAndExit();
        }

        $quiz = $quizGateway->getQuiz($quizId);
        if (!$quiz) {
            $this->flashMessageHelper->info($this->translator->trans('quiz.edit.invalid_id'));
            $this->routeHelper->goAndExit('/?page=dashboard');
        }

        if (!$quizPermissions->maySeeQuizData($quizId)) {
            $this->flashMessageHelper->info($this->translator->trans('quiz.edit.no_permission'));
            $this->routeHelper->goAndExit('/?page=dashboard');
        }

        $this->pageHelper->addTitle('Titel');
        $vue = $this->prepareVueComponent('vue-quiz-editor', 'QuizEditor', [
            'quizId' => $quizId,
            'visibleQuizes' => $quizPermissions->listQuizesForEditor(),
        ]);
        $this->pageHelper->addContent($vue);

        return $this->renderGlobal();
    }
}
