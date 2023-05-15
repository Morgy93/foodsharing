<?php

namespace Foodsharing\Modules\StoreChain\DTO;

use DateTime;
use Foodsharing\Modules\Foodsaver\DTO\FoodsaverForAvatar;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

/**
 * Class that represents the data of a store chain, in a format in which it is sent to the client.
 * This is not an entity class, it does not provide any domain logic nor does it contain any access
 * logic. You can see it more like a Data Transfer Object (DTO) used to pass a chains data between
 * parts of the application in a unified format.
 *
 * @OA\Schema(required={"id", "name", "status", "modification_date", "allow_press", "stores", "kams"})
 */
class StoreChainForChainList
{
    /**
     * Unique identifier of the chain.
     *
     * @OA\Property(example=1)
     */
    public int $id;

    /**
     * Name of the chain.
     *
     * @OA\Property(example="MyChain GmbH")
     */
    public string $name;

    /**
     * Indicates the cooperation status of this chain.
     * - '0' - Not Cooperating
     * - '1' - Waiting, i.e. in negotiation
     * - '2' - Cooperating.
     *
     * @OA\Property(enum={0, 1, 2}, example=2)
     */
    public int $status;

    /**
     * ZIP code of the chains headquater.
     *
     * @OA\Property(example="48149", nullable=true)
     */
    public ?string $headquarters_zip;

    /**
     * City of the chains headquater.
     *
     * @OA\Property(example="Münster", nullable=true)
     */
    public ?string $headquarters_city;

    /**
     * Date of the last update to this chains entry.
     *
     * @OA\Property(example="2022-08-04T00:00:00+02:00")
     */
    public DateTime $modification_date;

    /**
     * Whether the chain can be referred to in press releases.
     */
    public bool $allow_press;

    /**
     * Identifier of a forum thread related to this chain.
     *
     * @OA\Property(example=12345)
     */
    public ?int $forum_thread;

    /**
     * Miscellaneous notes.
     *
     * @OA\Property(example="Cooperating since 2021", nullable=true)
     */
    public ?string $notes;

    /**
     * Information about the chain to be displayed on every related stores page.
     *
     * @OA\Property(example="Pickup times between 10:00 and 12:15", nullable=true)
     */
    public ?string $common_store_information;

    /**
     * The number of stores that are part of this chain.
     *
     * @OA\Property(example=5)
     */
    public int $store_count;

    /**
     * Key account managers.
     *
     * @OA\Property(
     * 	type="array",
     * 	description="Managers of this chain",
     * 	@OA\Items(ref=@Model(type=FoodsaverForAvatar::class))
     * )
     */
    public array $kams;
}
