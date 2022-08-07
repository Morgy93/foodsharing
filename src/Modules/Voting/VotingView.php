<?php

namespace Foodsharing\Modules\Voting;

use DateTime;
use Foodsharing\Modules\Core\DBConstants\Unit\UnitType;
use Foodsharing\Modules\Core\View;
use Foodsharing\Modules\Voting\DTO\Poll;

class VotingView extends View
{
	public function pollOverview(Poll $poll, array $region, bool $mayVote, ?DateTime $userVoteDate, bool $mayEdit): string
	{
		return $this->vueComponent('poll-overview', 'pollOverview', [
			'poll' => $poll,
			'regionName' => $region['name'],
			'isWorkGroup' => $region['type'] === UnitType::WORKING_GROUP,
			'mayVote' => $mayVote,
			'userVoteDate' => $userVoteDate,
			'mayEdit' => $mayEdit
		]);
	}

	public function newPollForm(array $region): string
	{
		return $this->vueComponent('new-poll-form', 'newPollForm', [
			'region' => $region,
			'isWorkGroup' => $region['type'] == UnitType::WORKING_GROUP
		]);
	}

	public function editPollForm(Poll $poll)
	{
		return $this->vueComponent('edit-poll-form', 'editPollForm', [
			'poll' => $poll,
		]);
	}
}
