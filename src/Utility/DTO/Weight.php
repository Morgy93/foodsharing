<?php

namespace Foodsharing\Utility\DTO;

use OpenApi\Annotations as OA;

/**
 * Describes the common store metadata.
 */
class Weight
{
    /**
     * Maximum count of slots per pickup.
     *
     * The count of slots are limited by the foodsharing platform.
     *
     * @OA\Property(format="int64", example=1)
     */
    public int $id = 0;

    public string $label = '';
}
