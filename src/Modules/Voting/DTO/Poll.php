<?php

namespace Foodsharing\Modules\Voting\DTO;

use DateTime;
use Foodsharing\Modules\Core\DBConstants\Voting\VotingScope;
use Foodsharing\Modules\Core\DBConstants\Voting\VotingType;

/**
 * Class that represents a voting or election process.
 */
class Poll
{
	/**
	 * Unique identifier of this poll.
	 */
	public int $id;

	/**
	 * A short description of the poll that can serve as a title.
	 */
	public string $name;

	/**
	 * A more detailed description of the topic of this poll.
	 */
	public string $description;

	/**
	 * The date at which this poll began.
	 */
	public DateTime $startDate;

	/**
	 * The date at which this poll will end or has ended.
	 */
	public DateTime $endDate;

	/**
	 * Identifier of the region or work group in which this poll takes place. Only members of that region are allowed
	 * to vote.
	 */
	public int $regionId;

	/**
	 * The scope is an additional constraint defining which user groups are allowed to vote. See {@link VotingScope}.
	 */
	public int $scope;

	/**
	 * The type defines how users can vote. Different types allow to vote for one or multiple choices or allow a score
	 * voting. See {@link VotingType}.
	 */
	public int $type;

	/**
	 * Id of the user who created this poll.
	 */
	public int $authorId;

	/**
	 * Options of the poll.
	 */
	public array $options;

	public static function create(
		int $id,
		string $name,
		string $description,
		DateTime $startDate,
		DateTime $endDate,
		int $regionId,
		int $scope,
		int $type,
		int $authorId,
		array $options
	) {
		$poll = new Poll();
		$poll->id = $id;
		$poll->name = $name;
		$poll->description = $description;
		$poll->startDate = $startDate;
		$poll->endDate = $endDate;
		$poll->regionId = $regionId;
		$poll->scope = $scope;
		$poll->type = $type;
		$poll->authorId = $authorId;
		$poll->options = $options;

		return $poll;
	}
}
