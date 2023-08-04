<?php

namespace Foodsharing\RestApi\Models\Notifications;

class NewsletterChat
{
    /**
     * Enable or disable newsletter.
     */
    public ?int $newsletter = 0;

    /**
     * Enable or disable mail notification for chat.
     */
    public ?int $chat = 0;
}
