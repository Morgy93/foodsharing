<?php

// table fs_store_log

namespace Foodsharing\Modules\Core\DBConstants\Store;

enum StoreLogAction: int
{
    case REQUEST_TO_JOIN = 1;
    case REQUEST_DECLINED = 2;
    case REQUEST_APPROVED = 3;
    case ADDED_WITHOUT_REQUEST = 4;
    case MOVED_TO_JUMPER = 5;
    case MOVED_TO_TEAM = 6;
    case REMOVED_FROM_STORE = 7;
    case LEFT_STORE = 8;
    case APPOINT_STORE_MANAGER = 9;
    case REMOVED_AS_STORE_MANAGER = 10;
    case SIGN_UP_SLOT = 11;
    case SIGN_OUT_SLOT = 12;
    case REMOVED_FROM_SLOT = 13;
    case SLOT_CONFIRMED = 14;
    case DELETED_FROM_WALL = 15;
    case REQUEST_CANCELLED = 16;
}
