<?php

namespace Foodsharing\RestApi\Models\QueryParser;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class QueryConditionStrategy
{
    abstract public function getOperator(): string;

    abstract public function checkValid(ValidatorInterface $validator, BasicFilterQuery $query, object $typeDef): ConstraintViolationListInterface;

    abstract public function generateSqlConditionStatement(BasicFilterQuery $query): string;

    abstract public function generateSqlValues(BasicFilterQuery $query): array;
}
