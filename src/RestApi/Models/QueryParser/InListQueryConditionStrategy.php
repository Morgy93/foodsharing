<?php

namespace Foodsharing\RestApi\Models\QueryParser;

use ReflectionClass;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InListQueryConditionStrategy extends QueryConditionStrategy
{
    public function __construct(private readonly BasicFilterQuery $query, private readonly string $className)
    {
    }

    public static function getOperator(): string
    {
        return 'in';
    }

    public function checkValid(ValidatorInterface $validator): ConstraintViolationListInterface
    {
        if (!property_exists($this->className, $this->query->field)) {
            return new ConstraintViolationList([new ConstraintViolation('Invalid field', null, [], '', $this->query->field, '')]);
        }

        if (empty($this->query->values)) {
            return new ConstraintViolationList([new ConstraintViolation('Empty query', null, [], '', $this->query->field, '')]);
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

        $placeholders = join(', ', array_fill(0, count($this->query->values), '?'));

        return '(' . $dbFieldName . ' IN ( ' . $placeholders . ' ))';
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
        $typeOfField = $property->getType();
        if (!$property->hasType() || !($typeOfField instanceof \ReflectionNamedType)) {
            return [];
        }
        if (!$typeOfField->isBuiltin()) {
            $ref = new ReflectionClass($typeOfField->getName());
            if ($ref->isEnum()) {
                $rEnum = new \ReflectionEnum($typeOfField->getName());

                return array_map(function ($i) use ($rEnum) { return $rEnum->getCase($i)->getValue(); }, $this->query->values);
            }
        }

        return array_map(function ($i) use ($typeOfField) {
            settype($i, $typeOfField->getName());

            return $i;
        }, $this->query->values);
    }
}
