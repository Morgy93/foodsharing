<?php

namespace Foodsharing\Modules\Uploads\DTO;

final class UploadedFile
{
    public function __construct(
        public string $filePath,
        readonly public int $fileSize,
        readonly public string $hashedBody,
        readonly public string $mimeType,
        readonly public int $uploaderId,
    ) {
    }
}
