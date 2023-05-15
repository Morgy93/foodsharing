<?php

namespace Foodsharing\Modules\Store\DTO;

class ContactData
{
    /**
     * String with name of contact person for store.
     */
    public ?string $name = '';

    /**
     * String with phone number of contact person for store.
     *
     * // Check phone number format?
     */
    public ?string $phone = '';

    /**
     * String with fax number of contact person for store.
     *
     * // Check phone number format?
     */
    public ?string $fax = '';

    /**
     * String with e-mail of contact person for store.
     *
     * // Check email? format
     */
    public ?string $email = '';

    public static function createFromArray($queryResult)
    {
        $obj = new ContactData();

        $obj->name = $queryResult['contactName'];
        $obj->phone = $queryResult['contactPhone'];
        $obj->fax = $queryResult['contactFax'];
        $obj->email = $queryResult['contactEmail'];

        return $obj;
    }
}
