<?php

namespace Foodsharing\RestApi\Models\Notifications;

class Region
{
    /**
     * Id for region.
     */
    public int $id = 0;

    /**
     * Emails from new forum threads in regions and working groups can be disabled.
     */
    public bool $notifyByEmailAboutNewThreads = false;
}
