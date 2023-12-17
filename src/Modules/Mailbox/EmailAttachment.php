<?php

namespace Foodsharing\Modules\Mailbox;

class EmailAttachment
{
    public const SIZE_UNKNOWN = 0;

    /**
     * Original file name used for displaying.
     */
    public string $fileName;
    /**
     * Unique hashed file name used for storing the file.
     */
    public string $hashedFileName;
    /**
     * File size in bytes.
     */
    public int $size;
    /**
     * Mime type of the attached file.
     */
    public string $mimeType;

    public function __construct()
    {
        $this->fileName = '';
        $this->hashedFileName = '';
        $this->size = -1;
        $this->mimeType = '';
    }

    public static function create(
        string $fileName,
        string $hashedFileName,
        int $size,
        string $mimeType
    ): EmailAttachment {
        $e = new EmailAttachment();
        $e->fileName = $fileName;
        $e->hashedFileName = $hashedFileName;
        $e->size = $size;
        $e->mimeType = $mimeType;

        return $e;
    }
}
