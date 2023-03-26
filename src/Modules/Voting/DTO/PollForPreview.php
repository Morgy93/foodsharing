<?php

namespace Foodsharing\Modules\Voting\DTO;

use Foodsharing\Modules\Core\DBConstants\Voting\VotingScope;

/**
 * Class that represents a voting or election process. Contains only field required for preview.
 */
class PollForPreview
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
     * The date at which this poll began.
     */
    public \DateTime $startDate;

    /**
     * The date at which this poll will end or has ended.
     */
    public \DateTime $endDate;

    /**
     * Identifier of the region or work group in which this poll takes place. Only members of that region are allowed
     * to vote.
     */
    public int $regionId;

    /**
     * Name of the region or work group in which this poll takes place.
     */
    public string $regionName;

    /**
     * The scope is an additional constraint defining which user groups are allowed to vote. See {@link VotingScope}.
     */
    public int $scope;

    /**
     * Whether the poll will only start in the future.
     */
    public bool $inFuture;

    public function __construct()
    {
        $this->id = -1;
        $this->name = '';
        $this->startDate = new \DateTime();
        $this->endDate = new \DateTime();
        $this->regionId = -1;
        $this->scope = -1;
        $this->inFuture = false;
    }

    public static function create(
        int $id,
        string $name,
        \DateTime $startDate,
        \DateTime $endDate,
        int $regionId,
        string $regionName,
        int $scope,
        bool $inFuture
    ) {
        $poll = new PollForPreview();
        $poll->id = $id;
        $poll->name = $name;
        $poll->startDate = $startDate;
        $poll->endDate = $endDate;
        $poll->regionId = $regionId;
        $poll->regionName = $regionName;
        $poll->scope = $scope;
        $poll->inFuture = $inFuture;

        return $poll;
    }
}
