<?php

namespace Foodsharing\Modules\Store\DTO;

class StoreTeamMembership
{
    public MinimalStoreIdentifier $store;
    public bool $isManaging;
    public int $membershipStatus;

    public static function createFromArray(array $query_result): StoreTeamMembership
    {
        $obj = new StoreTeamMembership();
        $obj->store = MinimalStoreIdentifier::createFromArray($query_result, 'store_');
        $obj->isManaging = $query_result['managing'] == 1;
        $obj->membershipStatus = $query_result['membership_status'];

        return $obj;
    }
}
