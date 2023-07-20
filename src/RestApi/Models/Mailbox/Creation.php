
<?php

namespace Foodsharing\RestApi\Models\Mailbox;

class Creation
{
    /**
     * Id for region.
     */
    public string $name;

    /**
     * Emails from new forum threads in regions and working groups can be disabled.
     */
    public string $alias;

    /**
     * Emails from new forum threads in regions and working groups can be disabled.
     */
    public array $userIds;
}
