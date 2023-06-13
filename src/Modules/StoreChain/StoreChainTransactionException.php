<?php

namespace Foodsharing\Modules\StoreChain;

class StoreChainTransactionException extends \Exception
{
    public const INVALID_STORECHAIN_ID = 'Store chain Id is not valid.';
    public const KEY_ACCOUNT_MANAGER_ID_NOT_EXISTS = 'Store chain key account manager does not exist.';
    public const KEY_ACCOUNT_MANAGER_ID_NOT_IN_GROUP = 'Store chain key account manager belong to AG Storechain.';
    public const THREAD_ID_NOT_EXISTS = 'Store chain thread does not exist.';
    public const WRONG_FORUM = 'Thread is from wrong forum.';
    public const EMPTY_NAME = 'Name can not be empty';
    public const EMPTY_CITY = 'City can not be empty';
    public const EMPTY_ZIP = 'Zip code can not be empty';
    public const INVALID_STATUS = 'status must be a valid status id';

    public function __construct(string $message = '', int $code = 0)
    {
        parent::__construct($message, $code);
    }

    public function __toString(): string
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
