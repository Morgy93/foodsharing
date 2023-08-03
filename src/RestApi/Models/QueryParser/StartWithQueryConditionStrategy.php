<?php

namespace Foodsharing\RestApi\Models\QueryParser;

use ReflectionClass;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class StartWithQueryConditionStrategy extends QueryConditionStrategy
{
    public function __construct(private readonly BasicFilterQuery $query, private readonly string $className)
    {
    }

    public static function getOperator(): string
    {
        return 'sw';
    }

    public function checkValid(ValidatorInterface $validator): ConstraintViolationListInterface
    {
        if (!property_exists($this->className, $this->query->field)) {
            return new ConstraintViolationList([new ConstraintViolation(
                'Invalid field', null, [], '', $this->query->field, $this->query->field, code: QueryConditionStrategy::INVALID_FIELD)]);
        }

        if (empty($this->query->values)) {
            return new ConstraintViolationList([new ConstraintViolation('Empty query', null, [], '', $this->query->field, '', code: QueryConditionStrategy::EMPTY_VALUE)]);
        }

        if (count($this->query->values) > 1) {
            return new ConstraintViolationList([new ConstraintViolation('Too many arguments', null, [], '', $this->query->field, '', code: QueryConditionStrategy::TO_MANY_VALUES)]);
        }

        $error = new ConstraintViolationList();
        foreach ($this->query->values as $value) {
            $error->addAll($validator->validatePropertyValue($this->className, $this->query->field, $value));
        }

        return $error;
    }

    public function generateSqlConditionStatement(): string
    {
        $dbFieldName = $this->query->field;

        $property = $this->getTypePropertyByFieldName();
        $dbFieldNameAttributes = $property->getAttributes(QueryDbFieldName::class);

        if (!empty($dbFieldNameAttributes)) {
            $attribute = $dbFieldNameAttributes[0]->newInstance();
            $dbFieldName = $attribute->fieldname;
        }

        return '(' . $dbFieldName . ' LIKE ?)';
    }

    private function getTypePropertyByFieldName(): \ReflectionProperty
    {
        $fieldname = $this->query->field;
        $class = new ReflectionClass($this->className);
        $property = current(array_filter($class->getProperties(), function ($property) use (&$fieldname) { return $property->getName() == $fieldname; }));

        return $property;
    }

    public function generateSqlValues(): array
    {
        $property = $this->getTypePropertyByFieldName();
        $value = $this->query->values[0];

        $typeOfField = $property->getType();
        if (!$property->hasType() || !($typeOfField instanceof \ReflectionNamedType)) {
            return [];
        }
        if (!$typeOfField->isBuiltin()) {
            $ref = new ReflectionClass($typeOfField->getName());
            if ($ref->isEnum()) {
                $rEnum = new \ReflectionEnum($typeOfField->getName());

                return [$rEnum->getCase($value)->getValue()];
            }
        }

        settype($value, $typeOfField->getName());

        if (is_string($value)) {
            $value .= '%';
        }

        return [$value];
    }
}
