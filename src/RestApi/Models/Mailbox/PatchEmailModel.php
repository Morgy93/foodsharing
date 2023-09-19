<?php

namespace Foodsharing\RestApi\Models\Mailbox;

/**
 * Contains all properties of an email that can be changed in the patch endpoint.
 */
class PatchEmailModel
{
    /**
     * @var int|null if not null, this specifies a folder to which the email shall be moved
     */
    public ?int $folder = null;

    /**
     * @var bool|null if not null, this sets the email's new read status
     */
    public ?bool $isRead = null;
}
