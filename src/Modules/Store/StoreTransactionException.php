<?php

namespace Foodsharing\Modules\Store;

class StoreTransactionException extends \Exception
{
    public const NO_PICKUP_SLOT_AVAILABLE = 'No pickup slot available';
    public const NO_PICKUP_OTHER_USER = 'No pickup for another users.';
    public const INVALID_REGION = 'Store is in an unknown region';
    public const INVALID_REGION_TYPE = 'Region type is wrong';

    public function __construct(string $message = '', int $code = 0)
    {
        parent::__construct($message, $code);
    }

    public function __toString(): string
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
