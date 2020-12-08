<?php

namespace Foodsharing\Modules\Foodsaver;

use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Quiz\QuizSessionGateway;
use Foodsharing\Modules\Store\StoreTransactions;

class FoodsaverTransactions
{
	private FoodsaverGateway $foodsaverGateway;
	private QuizSessionGateway $quizSessionGateway;
	private StoreTransactions $storeTransactions;

	public function __construct(
		FoodsaverGateway $foodsaverGateway,
		QuizSessionGateway $quizSessionGateway,
		StoreTransactions $storeTransactions
	) {
		$this->foodsaverGateway = $foodsaverGateway;
		$this->quizSessionGateway = $quizSessionGateway;
		$this->storeTransactions = $storeTransactions;
	}

	public function downgradeAndBlockForQuizPermanently(int $fsId): int
	{
		$this->quizSessionGateway->blockUserForQuiz($fsId, Role::FOODSAVER);

		$this->storeTransactions->leaveAllStoreTeams($fsId);

		return $this->foodsaverGateway->downgradePermanently($fsId);
	}
}
