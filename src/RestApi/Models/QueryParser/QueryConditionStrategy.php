<?php

namespace Foodsharing\RestApi\Models\QueryParser;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class QueryConditionStrategy
{
    public const INVALID_FIELD = '3458e444-ad84-42e1-9016-247d24c8ea9a';
    public const EMPTY_VALUE = '1c4021ce-0f4d-4e0a-a5c3-10bfc614b8b6';
    public const TO_MANY_VALUES = 'e06dd23d-1554-499a-b6b8-c51a87b460d2';

    abstract public static function getOperator(): string;

    abstract public function checkValid(ValidatorInterface $validator): ConstraintViolationListInterface;

    abstract public function generateSqlConditionStatement(): string;

    abstract public function generateSqlValues(): array;
}
