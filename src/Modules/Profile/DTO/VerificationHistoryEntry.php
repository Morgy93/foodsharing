<?php

namespace Foodsharing\Modules\Profile\DTO;

use DateTime;
use Foodsharing\Modules\Foodsaver\Profile;

/**
 * Represents the event of a foodsaver being (de-)verified by an ambassador. These history entries are intended to be
 * shown on the profile page.
 */
class VerificationHistoryEntry
{
    /**
     * Id of the foodsaver who was (de-)verified.
     */
    public int $foodsaverId = 0;

    /**
     * Date and time at which the (de-)verification was done.
     */
    public DateTime $date;

    /**
     * Whether the foodsaver was verified (true) or deverified (false).
     */
    public bool $wasVerified = false;

    /**
     * The ambassador who did the (de-)verification. This can be null if, for example, the ambassador's profile does
     * not exist anymore.
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
        bool $wasVerified,
        ?Profile $actor
    ): VerificationHistoryEntry {
        $v = new VerificationHistoryEntry();
        $v->foodsaverId = $foodsaverId;
        $v->date = $date;
        $v->wasVerified = $wasVerified;
        $v->actor = $actor;

        return $v;
    }
}
