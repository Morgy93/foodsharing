<?php

namespace Foodsharing\Modules\StoreChain;

class StoreChainTransactionException extends \Exception
{
    public const INVALID_STORECHAIN_ID = 'Store chain Id is not valid.';
    public const KEY_ACCOUNT_MANAGER_ID_NOT_EXISTS = 'Store chain key account manager id does not exist.';

    public function __construct(string $message = '', int $code = 0)
    {
        parent::__construct($message, $code);
    }

    public function __toString(): string
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
