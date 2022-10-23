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
	 * Date at which this poll was created.
	 */
	public DateTime $creationDate;

	/**
	 * The number of different values that each option is this poll can have.
	 */
	public int $numValues;

	/**
	 * Number of users who have voted. A value of null means that the results are not included in this poll object.
	 */
	public ?int $votes;

	/**
	 * Number of users who are eligible to vote.
	 */
	public int $eligibleVotesCount;

	/**
	 * Options of the poll. The array maps the option indices to the object and the indices will always be ascending
	 * integers starting at 0.
	 *
	 * @var PollOption[]
	 */
	public array $options;

	/**
	 * If true, options should be shown in a random order by the frontend.
	 */
	public bool $shuffleOptions;

	public function __construct()
	{
		$this->id = -1;
		$this->name = '';
		$this->description = '';
		$this->startDate = new DateTime();
		$this->endDate = new DateTime();
		$this->regionId = -1;
		$this->scope = -1;
		$this->type = -1;
		$this->authorId = -1;
		$this->creationDate = new DateTime();
		$this->numValues = 0;
		$this->votes = null;
		$this->eligibleVotesCount = 0;
		$this->options = [];
		$this->shuffleOptions = true;
	}

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
		DateTime $creationDate,
		int $numValues,
		?int $votes,
		int $eligibleVotesCount,
		array $options,
		bool $shuffleOptions
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
		$poll->creationDate = $creationDate;
		$poll->numValues = $numValues;
		$poll->votes = $votes;
		$poll->eligibleVotesCount = $eligibleVotesCount;
		$poll->options = $options;
		$poll->shuffleOptions = $shuffleOptions;

		return $poll;
	}
}
