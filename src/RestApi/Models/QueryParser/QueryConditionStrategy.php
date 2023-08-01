<?php

namespace Foodsharing\RestApi\Models\QueryParser;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class QueryConditionStrategy
{
    abstract public static function getOperator(): string;

    abstract public function checkValid(ValidatorInterface $validator): ConstraintViolationListInterface;

    abstract public function generateSqlConditionStatement(): string;

    abstract public function generateSqlValues(): array;
}
