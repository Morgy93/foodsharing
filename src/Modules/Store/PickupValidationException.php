<?php

namespace Foodsharing\Modules\Store;

use Exception;
use Throwable;

class PickupValidationException extends Exception
{
	public const PICK_UP_DATE_IN_THE_PAST = 'Pickup is in the past.';
	public const SLOT_COUNT_OUT_OF_RANGE = 'Slot value out of range.';
	public const MORE_OCCUPIED_SLOTS = 'Slot value is smaller then occuied slots.';
	public const INVALID_STORE = 'Store does not exist.';

	public function __construct(string $message = '', int $code = 0, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}

	public function __toString(): string
	{
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}
}
