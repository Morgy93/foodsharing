<?php

namespace Foodsharing\Permissions;

use Exception;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Quiz\QuizIDs;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Quiz\QuizGateway;

final class QuizPermissions
{
    private Session $session;
    private QuizGateway $quizGateway;

    public function __construct(
        Session $session,
        QuizGateway $quizGateway
    ) {
        $this->session = $session;
        $this->quizGateway = $quizGateway;
    }

    // public function mayEditQuiz(): bool
    // {
    // return $this->session->mayRole(Role::ORGA) || $this->session->isAdminFor(RegionIDs::QUIZ_AND_REGISTRATION_WORK_GROUP);
    // }

    public function mayEditQuiz(?int $quizId = 0): bool
    {
        if ($this->session->mayRole(Role::ORGA)) {
            return true;
        }
        switch ($quizId) {
            case QuizIDs::FOODSAVER:
            case QuizIDs::STORE_MANAGER:
                return $this->session->isAdminFor(RegionIDs::QUIZ_AND_REGISTRATION_WORK_GROUP);
            case QuizIDs::AMBASSADOR:
                return $this->session->isAdminFor(RegionIDs::BOT_WELCOME_TEAM);
            case QuizIDs::KEY_ACCOUNT_MANAGER:
                return $this->session->isAdminFor(RegionIDs::STORE_CHAIN_GROUP);
            case QuizIDs::HYGIENE:
                return $this->session->isAdminFor(RegionIDs::HYGIENE_GROUP);
        }
        return false;
    }

    public function maySeeQuizData(int $quizId): bool
    {
        if ($this->mayEditQuiz($quizId)) {
            return true;
        }
        
        switch ($quizId) {
            case QuizIDs::FOODSAVER:
                return $this->session->mayRole(Role::FOODSAVER) && $this->session->isMemberIn(RegionIDs::QUIZ_AND_REGISTRATION_WORK_GROUP);
            case QuizIDs::STORE_MANAGER:
                return $this->session->mayRole(Role::STORE_MANAGER) && $this->session->isMemberIn(RegionIDs::QUIZ_AND_REGISTRATION_WORK_GROUP);
            case QuizIDs::AMBASSADOR:
                return $this->session->mayRole(Role::AMBASSADOR) && $this->session->isMemberIn(RegionIDs::BOT_WELCOME_TEAM);
            default:
                return false;
        }
    }

    public function listQuizesForEditor(): array
    {
        $visible = array_filter(QuizIDs::getConstants(), function($quizId) {
            return $this->maySeeQuizData($quizId);
        });
        return array_values(array_map(function($quizId) {
            return [
                'id' => $quizId,
                'edit' => $this->mayEditQuiz($quizId),
                'name' => $this->quizGateway->getQuiz($quizId)['name'],
            ];
        }, $visible));
    }
}
