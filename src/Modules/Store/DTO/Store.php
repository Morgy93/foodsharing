<?php

namespace Foodsharing\Modules\Store\DTO;

use DateTime;
use Foodsharing\Modules\Core\DBConstants\Store\ConvinceStatus;
use Foodsharing\Modules\Core\DBConstants\Store\CooperationStatus;
use Foodsharing\Modules\Core\DBConstants\Store\PublicTimes;
use Foodsharing\Modules\Core\DBConstants\Store\TeamSearchStatus;
use Foodsharing\Modules\Core\DTO\GeoLocation;
use InvalidArgumentException;

/**
 * This class is a representation of an Store.
 */
class Store
{
    /**
     * Unique identifier of the store in database.
     */
    public int $id;

    /**
     * Name of the store.
     */
    public string $name;

    /**
     * Region which is manages and is responsible for this store.
     */
    public int $regionId;

    /**
     * Location of the store.
     */
    public GeoLocation $location;

    /**
     * Street name with street number.
     */
    public Address $address;

    /**
     * Public information about the store which is visible
     * for users which are looking for a store.
     *
     * Max length 180 chars
     */
    public string $publicInfo;

    /**
     * Enum which represents the expected pickup time range.
     */
    public PublicTimes $publicTime;

    /**
     * Identifier of the store category.
     *
     * @see StoreGateway::getStoreCategories()
     */
    public ?int $categoryId = null;

    /**
     * Identifier of the store chain.
     *
     * @see StoreGateway::getBasics_chain()
     */
    public ?int $chainId = null;

    /**
     * Enum which represents the current state of cooperation between foodsharing and store.
     */
    public ?CooperationStatus $cooperationStatus = CooperationStatus::UNCLEAR;

    /**
     * Date of cooperation between store and foodsharing.
     */
    public ?DateTime $cooperationStart = null;

    /**
     * String which describes the store.
     */
    public string $description;

    /**
     * Contact information for store contact.
     */
    public ContactData $contact;

    /**
     * Duration in seconds before user can register to a pickup slot before pickup.
     *
     * - 0 : not set
     * - 604800: 1 Week
     * - 1209600: 2 Weeks
     * - 1814400: 3 Weeks
     * - 2419200: 4 Weeks
     */
    public int $calendarInterval = 0;

    /**
     * Enum which category of weight per pickup.
     *
     * - 0: UNCLEAR
     * - 1: 1-3 kg
     * - 2: 3-5 kg
     * - 3: 5-10 kg
     * - 4: 10-20 kg
     * - 5: 20-30 kg
     * - 6: 40-50 kg
     * - 7: more then 50 kg
     *
     * @Assert\Range(min=0, max=7)
     */
    public int $weight = 0;

    /**
     * Enum which represents the effort to create the cooperation betwee store and foodsharing.
     */
    public ConvinceStatus $effort = ConvinceStatus::NOT_SET;

    /**
     * Boolean which represents that store allow using for foodsharing publicity.
     */
    public bool $publicity = false;

    /**
     * Boolean which mark store that they shows foodsharing sticker on the store.
     */
    public bool $showsSticker = false;

    /**
     * List of grocerie which are provided by the store.
     *
     * @var int[] List of grocerie which are provided by the store
     *
     * @Type("array<int>")
     */
    public array $groceries = [];

    /**
     * Status of team.
     */
    public TeamSearchStatus $teamStatus = TeamSearchStatus::CLOSED;

    /**
     * Configuration option to influence behavior of store.
     */
    public StoreOptionModel $options;

    public DateTime $createdAt;
    public DateTime $updatedAt;

    public function __construct()
    {
        $this->location = new GeoLocation();
        $this->address = new Address();
        $this->options = new StoreOptionModel();
        $this->contact = new ContactData();
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    public static function createFromArray($queryResult): Store
    {
        $obj = new Store();
        $obj->id = $queryResult['id'];
        $obj->name = $queryResult['name'];
        $obj->regionId = $queryResult['regionId'];

        try {
            $obj->location = GeoLocation::createFromArray($queryResult);
        } catch (InvalidArgumentException) {
            $obj->location = new GeoLocation();
        }
        $obj->address->street = $queryResult['street'];
        $obj->address->zipCode = $queryResult['zipCode'];
        $obj->address->city = $queryResult['city'];

        $obj->publicInfo = isset($queryResult['public_info']) ? $queryResult['public_info'] : '';
        $obj->publicTime = PublicTimes::tryFrom($queryResult['public_time']);

        $obj->categoryId = $queryResult['categoryId'];
        $obj->chainId = $queryResult['chainId'];

        $obj->cooperationStatus = CooperationStatus::tryFrom($queryResult['cooperationStatus']);
        if ($queryResult['cooperationStart']) {
            $cooperationStart = DateTime::createFromFormat('Y-m-d', $queryResult['cooperationStart']);
            if ($cooperationStart) {
                $obj->cooperationStart = $cooperationStart;
            }
        }

        $obj->description = $queryResult['description'] ?? '';

        $obj->contact = ContactData::createFromArray($queryResult);

        $obj->calendarInterval = $queryResult['calendarInterval'];
        $obj->weight = $queryResult['weight'];
        $obj->effort = ConvinceStatus::tryFrom($queryResult['effort']);

        $obj->publicity = $queryResult['publicity'] == 1;
        $obj->showsSticker = $queryResult['sticker'] == 1;

        $obj->teamStatus = TeamSearchStatus::tryFrom($queryResult['teamStatus']);

        $obj->options = StoreOptionModel::createFromArray($queryResult);

        $createdAt = DateTime::createFromFormat('Y-m-d', $queryResult['createdAt']);
        if ($createdAt) {
            $obj->createdAt = $createdAt;
        }

        if ($queryResult['updatedAt']) {
            $obj->updatedAt = DateTime::createFromFormat('Y-m-d', $queryResult['updatedAt']);
        } else {
            $obj->updatedAt = $obj->createdAt;
        }

        if (isset($queryResult['groceries']) && is_array($queryResult['groceries'])) {
            $obj->groceries = $queryResult['groceries'];
        }

        return $obj;
    }
}
