<?php

namespace Foodsharing\Modules\Store\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class PatchAddress
{
    /**
     * String with street and street number.
     *
     * @Assert\Length(max=120)
     */
    public ?string $street = null;

    /**
     * String with city name.
     *
     * @Assert\Length(max=50)
     */
    public ?string $city = null;

    /**
     * String with zip code of store.
     *
     * @Assert\Length(max=5)
     */
    public ?string $zipCode = null;

    public static function apply(PatchAddress &$addressChange, Address &$storeAddress): bool
    {
        $patchNeeded = false;
        if (!empty($addressChange->street)) {
            $patchNeeded = true;
            $storeAddress->street = $addressChange->street;
        }
        if (!empty($addressChange->city)) {
            $patchNeeded = true;
            $storeAddress->city = $addressChange->city;
        }
        if (!empty($addressChange->zipCode)) {
            $patchNeeded = true;
            $storeAddress->zipCode = $addressChange->zipCode;
        }

        return $patchNeeded;
    }
}
