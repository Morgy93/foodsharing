<?php

namespace Foodsharing\RestApi\Models\QueryParser;

use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class QueryContrainstBuilder
{
    /**
     * @param string[] $rawQueries V
     */
    public function validate(ValidatorInterface $validator, array $rawQueries, object $typedef): ConstraintViolationListInterface
    {
        $strategies = [new InListQueryConditionStrategy()];
        $preprocessed = [];
        foreach ($rawQueries as $rawQuery) {
            $preprocessed[] = BasicFilterQuery::decodeRawQuery($rawQuery);
        }

        $errors = new ConstraintViolationList();
        foreach ($preprocessed as $query) {
            $strategy = current(array_filter($strategies, function ($strategy) use (&$query) { return $strategy->getOperator() == $query->operator; }));

            if ($strategy) {
                $errors->addAll($strategy->checkValid($validator, $query, $typedef));
            }
        }

        return $errors;
    }
}
