<?php

namespace Foodsharing\Modules\Uploads\DTO;

final class Image
{
    public function __construct(
        readonly public string $filePath,
        readonly public int $fileSize,
        readonly public string $hashedBody,
        readonly public string $mimeType,
    ) {
    }
}
