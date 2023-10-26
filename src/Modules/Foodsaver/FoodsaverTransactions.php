<?php

namespace Foodsharing\Modules\Foodsaver;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Basket\BasketGateway;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Message\MessageTransactions;
use Foodsharing\Modules\Quiz\QuizSessionGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\StoreTransactions;

class FoodsaverTransactions
{
    private FoodsaverGateway $foodsaverGateway;
    private QuizSessionGateway $quizSessionGateway;
    private BasketGateway $basketGateway;
    private StoreTransactions $storeTransactions;
    private Session $session;
    private RegionGateway $regionGateway;
    private MessageTransactions $messageTransactions;

    public function __construct(
        FoodsaverGateway $foodsaverGateway,
        QuizSessionGateway $quizSessionGateway,
        BasketGateway $basketGateway,
        StoreTransactions $storeTransactions,
        Session $session,
        RegionGateway $regionGateway,
        MessageTransactions $messageTransactions
    ) {
        $this->foodsaverGateway = $foodsaverGateway;
        $this->quizSessionGateway = $quizSessionGateway;
        $this->basketGateway = $basketGateway;
        $this->storeTransactions = $storeTransactions;
        $this->session = $session;
        $this->regionGateway = $regionGateway;
        $this->messageTransactions = $messageTransactions;
    }

    public function downgradeAndBlockForQuizPermanently(int $fsId): int
    {
        $this->quizSessionGateway->blockUserForQuiz($fsId, Role::FOODSAVER);

        $this->storeTransactions->leaveAllStoreTeams($fsId);

        return $this->foodsaverGateway->downgradePermanently($fsId);
    }

    public function deleteFoodsaver(int $foodsaverId, ?string $reason): void
    {
        // set all active baskets of the user to deleted
        $this->basketGateway->removeActiveUserBaskets($foodsaverId);

        $this->storeTransactions->leaveAllStoreTeams($foodsaverId);

        // delete the user
        $this->foodsaverGateway->deleteFoodsaver($foodsaverId, $this->session->id(), $reason);
    }

    public function deleteFromRegion(int $regionId, ?int $fsId, int $actorId, ?string $message): void
    {
        $this->foodsaverGateway->deleteFromRegion($regionId, $fsId, $actorId, $message);
        
        if ($fsId !== $actorId) {
            $params = ['{regionName}' => $this->regionGateway->getRegionName($regionId)];
            $this->messageTransactions->sendRequiredMessageToUser($fsId, $actorId, 'kick_from_region', $message, $params);
        }
    }
}
