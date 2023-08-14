<?php

namespace Foodsharing\Modules\StoreChain;

enum StoreChainStatus: int
{
    case NOT_COOPERATING = 0;
    case WAITING = 1;
    case COOPERATING = 2;
}
