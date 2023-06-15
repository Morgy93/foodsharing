<?php

namespace Foodsharing\RestApi\Models\Region;

use Foodsharing\Modules\Core\DBConstants\Region\WorkgroupFunction;
use Foodsharing\Modules\Core\DBConstants\Unit\UnitType;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Contains all properties of a region that can be updated in the region admin module.
 */
class RegionUpdateModel
{
    /**
     * The region's new name.
     *
     * @Assert\NotBlank()
     * @OA\Property(example="Testbezirk")
     */
    public string $name = '';

    /**
     * The region's mailbox as used in the email address.
     *
     * @Assert\NotBlank()
     * @OA\Property(example="testgruppe")
     */
    public string $mailbox = '';

    /**
     * The sender name for the region's mailbox.
     *
     * @OA\Property(example="Testgruppe")
     */
    public ?string $mailboxName = null;

    /**
     * The region type.
     *
     * @see UnitType
     *
     * @Assert\Type("integer")
     * @OA\Property(example=UnitType::CITY)
     */
    public int $type = UnitType::UNDEFINED;

    /**
     * Special function type of the working group. A value of null means that the working group does not have a
     * function. The value will be ignored for any other region type.
     *
     * @see WorkgroupFunction
     *
     * @OA\Property(example=WorkgroupFunction::WELCOME)
     */
    public ?int $workingGroupFunction = null;
}
