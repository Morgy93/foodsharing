<?php

namespace Foodsharing\Modules\Store\DTO;

use DateTime;
use Foodsharing\Modules\Core\DBConstants\Store\CooperationStatus;
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
    public string $street;

    /**
     * Zip code.
     */
    public string $zip;

    /**
     * City name.
     */
    public string $city;

    /**
     * Public information about the store which is visible
     * for users which are looking for a store.
     *
     * Max length 180 chars
     */
    public string $publicInfo;
    public ?int $publicTime;

    /**
     * Category of store.
     *
     * @see StoreGateway::getStoreCategories()
     */
    public ?int $categoryId;

    /**
     * Store chain information.
     *
     * @see StoreGateway::getBasics_chain()
     */
    public ?int $chainId;

    /**
     * Current status of cooperation between foodsharing and store.
     */
    public ?CooperationStatus $cooperationStatus;

    public ?string $description;
    // public array $foodTypes; // specialcased in StoreTransaction

    public ?string $contactName = null;
    public ?string $contactPhone = null;
    public ?string $contactFax = null;
    public ?string $contactEmail = null;
    public ?DateTime $cooperationStart = null;

    public ?int $calendarInterval = null;
    public ?int $useRegionPickupRule = null;
    public ?int $weight = null;
    public ?int $effort = null;
    public ?bool $publicity = null;
    public ?bool $sticker = null;

    public ?DateTime $createdAt = null;
    public DateTime $updatedAt;

    public static function createFromArray($queryResult): Store
    {
        $obj = new Store();
        $obj->id = $queryResult['id'];
        $obj->name = $queryResult['name'];
        $obj->regionId = $queryResult['region_id'];

        try {
            $obj->location = GeoLocation::createFromArray($queryResult);
        } catch (InvalidArgumentException) {
            $obj->location = new GeoLocation();
        }

        $obj->street = $queryResult['street'];
        $obj->zip = $queryResult['zip'];
        $obj->city = $queryResult['city'];
        $obj->publicInfo = isset($queryResult['public_info']) ? $queryResult['public_info'] : '';
        $obj->publicTime = $queryResult['public_time'];
        $obj->categoryId = $queryResult['categoryId'];
        $obj->chainId = $queryResult['chainId'];
        $obj->cooperationStatus = CooperationStatus::tryFrom($queryResult['cooperationStatus']);

        $obj->description = $queryResult['description'];

        // some fields are missing is missing

        $obj->publicity = $queryResult['publicity'] == 1;
        $obj->sticker = $queryResult['sticker'] == 1;

        $createAt = DateTime::createFromFormat('Y-m-d', $queryResult['createdAt']);
        if ($createAt) {
            $obj->createdAt = $createAt;
        }

        if ($queryResult['updatedAt']) {
            $obj->updatedAt = DateTime::createFromFormat('Y-m-d', $queryResult['updatedAt']);
        } else {
            $obj->updatedAt = $obj->createdAt;
        }

        return $obj;
    }
}
