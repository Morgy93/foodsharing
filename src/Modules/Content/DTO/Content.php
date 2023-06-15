<?php

namespace Foodsharing\Modules\Content\DTO;

use DateTime;

/**
 * Represents one entry in the fs_content table.
 */
class Content
{
    public string $title;

    public string $body;

    /**
     * Date and time in UTC timezone at which this content was modified.
     */
    public ?DateTime $lastModified;

    public static function create(string $title, string $body, ?DateTime $lastModified): Content
    {
        $c = new Content();
        $c->title = $title;
        $c->body = $body;
        $c->lastModified = $lastModified;

        return $c;
    }
}
