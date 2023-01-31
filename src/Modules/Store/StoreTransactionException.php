<?php

namespace Foodsharing\Modules\Store;

class StoreTransactionException extends \Exception
{
    public const NO_PICKUP_SLOT_AVAILABLE = 'No pickup slot available';
    public const NO_PICKUP_OTHER_USER = 'No pickup for another users.';
    public const STORE_CATEGORY_NOT_EXISTS = 'Store category does not exists.';
    public const STORE_CHAIN_NOT_EXISTS = 'Store chain does not exists.';
    public const INVALID_STORE_TEAM_STATUS = 'Team status is invalid';
    public const INVALID_PUBLIC_TIMES = 'Public time is invalid';
    public const INVALID_COOPERATION_STATUS = 'Cooperation status is invalid.';
    public const INVALID_CONVINCE_STATUS = 'Effort convince status is invalid.';
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
