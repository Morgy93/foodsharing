<?php

namespace Foodsharing\RestApi\Models\Notifications;

use Foodsharing\Modules\Core\DBConstants\Info\InfoType;

class FoodSharePoint
{
    /**
     * Id for foodSharePoint.
     */
    public int $id = 0;

    /**
     * Following type for food share points.
     */
    public int $infotype = InfoType::NONE;
}
