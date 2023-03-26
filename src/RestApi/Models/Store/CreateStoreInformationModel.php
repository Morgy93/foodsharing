<?php

namespace Foodsharing\RestApi\Models\Store;

use Foodsharing\Modules\Core\DTO\GeoLocation;
use Foodsharing\Modules\Store\DTO\CreateStoreData;
use Foodsharing\Validator\NoHtml;
use Symfony\Component\Validator\Constraints as Assert;

class CreateStoreInformationModel
{
    /**
     * Name of the store.
     *
     * @Assert\NotNull()
     * @Assert\Length(max=120)
     *
     * @NoHtml
     */
    public ?string $name;

    /**
     * Location of the store.
     *
     * @Assert\NotNull()
     * @Assert\Valid()
     */
    public ?GeoLocation $location;

    /**
     * Street name with street number.
     *
     * @Assert\NotNull()
     * @Assert\Length(max=120)
     *
     * @NoHtml
     */
    public ?string $street;

    /**
     * Zip code.
     *
     * @Assert\NotNull()
     * @Assert\Length(max=5)
     */
    public ?string $zipCode;

    /**
     * City name.
     *
     * @Assert\NotNull()
     * @Assert\Length(max=50)
     *
     * @NoHtml
     */
    public ?string $city;

    /**
     * Public information about the store which is visible
     * for users which are looking for a store.
     *
     * @Assert\NotNull()
     * @Assert\Length(max=200)
     *
     * @NoHtml
     */
    public ?string $publicInfo;

    public function toCreateStore(): CreateStoreData
    {
        $store = new CreateStoreData();
        $store->name = $this->name;
        $store->location = $this->location;
        $store->street = $this->street;
        $store->zipCode = $this->zipCode;
        $store->city = $this->city;
        $store->publicInfo = $this->publicInfo;

        return $store;
    }
}
