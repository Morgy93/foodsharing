<?php

namespace Foodsharing\RestApi\Models\QueryParser;

use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InListQueryConditionStrategy extends QueryConditionStrategy
{
    public function getOperator(): string
    {
        return 'in';
    }

    public function checkValid(ValidatorInterface $validator, BasicFilterQuery $query, object $typeDef): ConstraintViolationListInterface
    {
        if (empty($query->values)) {
            return false;
        }

        if(!property_exists($typeDef, $query->field)) {
            return false;
        }

        $error = new ConstraintViolationList();
        foreach ($query->values as $value) {
            $values = explode(',', $value);
            foreach ($values as $v) {
                $error->addAll($validator->validatePropertyValue($typeDef, $query->field, $v));
            }
        }

        return $error;
    }

    public function generateSqlConditionStatement(BasicFilterQuery $query): string
    {
        return '(' . $query->field . ' IN ( ?)';
    }

    public function generateSqlValues(BasicFilterQuery $query): array
    {
        return ['<v>'];
    }
}
