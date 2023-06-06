<?php

namespace Foodsharing\Modules\StoreChain\DTO;

use DateTime;
use DateTimeZone;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Foodsaver\DTO\FoodsaverForAvatar;
use Foodsharing\Modules\StoreChain\StoreChainStatus;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class that represents the data of a store chain, in a format in which it is sent to the client.
 * This is not an entity class, it does not provide any domain logic nor does it contain any access
 * logic. You can see it more like a Data Transfer Object (DTO) used to pass a chains data between
 * parts of the application in a unified format.
 */
class StoreChain
{
    /**
     * Unique identifier of the chain.
     *
     * @OA\Property(example=1, readOnly=true)
     */
    public ?int $id;

    /**
     * Name of the chain.
     *
     * @OA\Property(example="MyChain GmbH")
     * @Assert\Length(max=120)
     */
    public string $name;

    /**
     * Indicates the cooperation status of this chain.
     * - '0' - Not Cooperating
     * - '1' - Waiting, i.e. in negotiation
     * - '2' - Cooperating.
     *
     * @OA\Property(enum={0, 1, 2}, example=2)
     * @Assert\Range (min = 0, max = 2)
     */
    public StoreChainStatus $status;

    /**
     * ZIP code of the chains headquater.
     *
     * @OA\Property(example="48149", nullable=true)
     * @Assert\Length(max=120)
     */
    public ?string $headquartersZip;

    /**
     * City of the chains headquater.
     *
     * @OA\Property(example="Münster", nullable=true)
     * @Assert\Length(max=50)
     */
    public ?string $headquartersCity;

    /**
     * Whether the chain can be referred to in press releases.
     */
    public bool $allowPress;

    /**
     * Identifier of a forum thread related to this chain.
     *
     * @OA\Property(example=12345)
     * @Assert\Range (min = 0)
     */
    public int $forumThread;

    /**
     * Miscellaneous notes.
     *
     * @OA\Property(example="Cooperating since 2021", nullable=true)
     * @Assert\Length(max=200)
     */
    public ?string $notes;

    /**
     * Information about the chain to be displayed on every related stores page.
     *
     * @OA\Property(example="Pickup times between 10:00 and 12:15", nullable=true)
     * @Assert\Length(max=16777215)
     */
    public ?string $commonStoreInformation;

    /**
     * Identifiers of key account managers.
     *
     * @var array<FoodsaverForAvatar> Array of store information
     *
     * @OA\Property(
     *        type="array",
     *        @OA\Items(ref=@Model(type=FoodsaverForAvatar::class))
     *      )
     */
    public array $kams = [];

    /**
     * Date of last modification.
     *
     * @OA\Property(readOnly=true)
     */
    public ?DateTime $modificationDate;

    /**
     * Region of store chain management
     *
     * @OA\Property(readOnly=true)
     */
    public ?int $regionId = RegionIDs::STORE_CHAIN_GROUP;

    public static function createFromArray(array $data): StoreChain
    {
        $obj = new StoreChain();
        $obj->id = $data['id'];
        $obj->name = $data['name'];
        $obj->status = StoreChainStatus::from($data['status']);
        $obj->allowPress = $data['allow_press'];
        $obj->headquartersZip = $data['headquarters_zip'];
        $obj->headquartersCity = $data['headquarters_city'];
        $obj->modificationDate = new DateTime($data['modification_date'], new DateTimeZone('Europe/Berlin'));
        $obj->forumThread = $data['forum_thread'] ?? 0;
        $obj->notes = $data['notes'];
        $obj->commonStoreInformation = $data['common_store_information'];
        $obj->kams = $data['kams'];

        return $obj;
    }
}
