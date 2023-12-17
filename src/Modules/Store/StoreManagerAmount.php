<?php

namespace Foodsharing\Modules\Store;

/**
 * Defines the maximum and minimum amount of store managers per store.
 * These boundaries can be ignored by admins of the store coordination group (or ambassadors, if none exist).
 */
enum StoreManagerAmount: int
{
    case MINIMUM = 1; // Shouldn't be increased, since newly created stores always have one store manager
    case MAXIMUM = 3;
}
