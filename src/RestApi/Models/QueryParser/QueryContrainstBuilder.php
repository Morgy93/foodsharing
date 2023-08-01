<?php

namespace Foodsharing\RestApi\Models\QueryParser;

use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class QueryContrainstBuilder
{
    public function __construct(private readonly string $referenceTypename)
    {
    }

    /**
     * @param string[] $rawQueries V
     */
    public function validate(ValidatorInterface $validator, array $rawQueries): ConstraintViolationListInterface
    {
        $strategies = $this->findQueryConditionStrategies($rawQueries);

        $errors = new ConstraintViolationList();
        foreach ($strategies as $strategy) {
            $errors->addAll($strategy->checkValid($validator));
        }

        return $errors;
    }

    /**
     * @return QueryConditionStrategy[]
     */
    public function findQueryConditionStrategies(array $rawQueries): array
    {
        $strategies = [InListQueryConditionStrategy::class];
        $preprocessed = [];
        foreach ($rawQueries as $rawQuery) {
            $preprocessed[] = BasicFilterQuery::decodeRawQuery($rawQuery);
        }

        $usedStrategies = [];
        foreach ($preprocessed as $query) {
            $strategy = current(array_filter($strategies, function ($strategy) use (&$query) { return $strategy::getOperator() == $query->operator; }));

            if ($strategy) {
                $usedStrategies[] = new $strategy($query, $this->referenceTypename);
            }
        }

        return $usedStrategies;
    }
}
