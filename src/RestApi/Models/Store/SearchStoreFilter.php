<?php

namespace Foodsharing\RestApi\Models\Store;

use Foodsharing\RestApi\Models\QueryParser\QueryDbFieldName;
use Symfony\Component\Validator\Constraints as Assert;

class SearchStoreFilter
{
    /**
     * Enum which represents the current state of cooperation between foodsharing and store.
     *
     * - 0: UNCLEAR
     * - 1: NO_CONTACT
     * - 2: IN_NEGOTIATION
     * - 3: COOPERATION_STARTING
     * - 4: DOES_NOT_WANT_TO_WORK_WITH_US
     * - 5:COOPERATION_ESTABLISHED
     * - 6: GIVES_TO_OTHER_CHARITY
     * - 7: PERMANENTLY_CLOSED
     *
     * @Assert\Range (min = 0, max = 7)
     */
    #[QueryDbFieldName('betrieb_status_id')]
    public ?int $cooperationStatus = null;

    /**
     * Enum which represent the state of searching members.
     *
     * - CLOSED = 0 No new members accepted
     * - OPEN = 1 Open for members
     * - OPEN_SEARCHING = 2 Requires new members
     *
     * @Assert\Range(min=0, max=2)
     */
    #[QueryDbFieldName('team_status')]
    public ?int $teamStatus = null;
}
