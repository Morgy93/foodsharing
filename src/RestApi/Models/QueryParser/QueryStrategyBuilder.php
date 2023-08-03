<?php

namespace Foodsharing\RestApi\Models\QueryParser;

use ReflectionClass;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class QueryStrategyBuilder
{
    public const NO_STRATEGY_FOUND = 'cb8e1804-29e6-43e5-809d-6bab0c92e0f8';

    private array $strategies = [];

    public function __construct(private readonly string $referenceTypename)
    {
        $ref = new ReflectionClass($referenceTypename);
        foreach ($ref->getProperties() as $property) {
            $name = $property->getName();
            $attributes = $property->getAttributes(SupportedQueryConditionStrategy::class);

            if (!empty($attributes)) {
                $supportedStrategies = $attributes[0]->newInstance();
                $this->strategies[$name] = $supportedStrategies->typenames;
            }
        }
    }

    /**
     * @param string[] $rawQueries V
     */
    public function validate(ValidatorInterface $validator, array $rawQueries): ConstraintViolationListInterface
    {
        $errors = new ConstraintViolationList();
        if (empty($rawQueries)) {
            return $errors;
        }

        $strategies = $this->findQueryConditionStrategies($rawQueries);
        if (count($strategies)) {
            foreach ($strategies as $strategy) {
                $errors->addAll($strategy->checkValid($validator));
            }
        } else {
            $errors->add(new ConstraintViolation('Invalid query operation or field.', '', [], null, '', $rawQueries, code: QueryStrategyBuilder::NO_STRATEGY_FOUND));
        }

        return $errors;
    }

    /**
     * @return QueryConditionStrategy[]
     */
    public function findQueryConditionStrategies(array $rawQueries): array
    {
        $preprocessed = [];
        foreach ($rawQueries as $rawQuery) {
            $preprocessed[] = BasicFilterQuery::decodeRawQuery($rawQuery);
        }

        $usedStrategies = [];
        foreach ($preprocessed as $query) {
            if (array_key_exists($query->field, $this->strategies)) {
                $allowedStrategies = $this->strategies[$query->field];
                $strategy = current(array_filter($allowedStrategies, function ($strategy) use (&$query) { return $strategy::getOperator() == $query->operator; }));

                if ($strategy) {
                    $usedStrategies[] = new $strategy($query, $this->referenceTypename);
                }
            } else {
                return [];
            }
        }

        return $usedStrategies;
    }
}
