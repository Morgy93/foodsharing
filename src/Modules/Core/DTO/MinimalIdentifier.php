<?php

namespace Foodsharing\Modules\Core\DTO;

/**
 * Describes a identifier of a entry.
 */
class MinimalIdentifier
{
    /**
     * Unique identifier of entry.
     */
    public int $id;

    public static function createFromId(?int $id): ?MinimalIdentifier
    {
        if ($id) {
            $entity = new MinimalIdentifier();
            $entity->id = $id;

            return $entity;
        }

        return null;
    }
}
