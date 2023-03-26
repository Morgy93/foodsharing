<?php

namespace Foodsharing\Modules\Profile\DTO;

use DateTime;
use Foodsharing\Modules\Foodsaver\Profile;

/**
 * Represents the event of a foodsaver's pass being created by an ambassador or by the foodsaver.
 * These history entries are intended to be shown on the profile page.
 */
class PassHistoryEntry
{
    /**
     * Id of the foodsaver who's pass was created.
     */
    public int $foodsaverId = 0;

    /**
     * Date and time at which the pass was created.
     */
    public DateTime $date;

    /**
     * The person who created the pass. This can be null if, for example, the ambassador's profile does not exist
     * anymore.
     */
    public ?Profile $actor;

    public function __construct()
    {
        $this->date = new DateTime();
        $this->actor = new Profile(0, null, null, 0);
    }

    public static function create(
        int $foodsaverId,
        DateTime $date,
        ?Profile $actor
    ): PassHistoryEntry {
        $p = new PassHistoryEntry();
        $p->foodsaverId = $foodsaverId;
        $p->date = $date;
        $p->actor = $actor;

        return $p;
    }
}
