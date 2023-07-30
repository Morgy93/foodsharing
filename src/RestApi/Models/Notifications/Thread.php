<?php

namespace Foodsharing\RestApi\Models\Notifications;

use Foodsharing\Modules\Core\DBConstants\Info\InfoType;

class Thread
{
    /**
     * Id for thread.
     */
    public int $id = 0;

    /**
     * Following type or threads.
     *
     * {@see InfoType}
     */
    public int $infotype = InfoType::NONE;
}
