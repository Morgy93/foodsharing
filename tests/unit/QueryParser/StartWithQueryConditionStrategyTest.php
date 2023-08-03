<?php

use Foodsharing\RestApi\Models\QueryParser\BasicFilterQuery;
use Foodsharing\RestApi\Models\QueryParser\QueryConditionStrategy;
use Foodsharing\RestApi\Models\QueryParser\QueryDbFieldName;
use Foodsharing\RestApi\Models\QueryParser\StartWithQueryConditionStrategy;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

enum DaysOfWeek: int
{
    case Sunday = 0;
    case Monday = 1;
    // etc.
}

class TestValidClass
{
    #[Assert\Range(
        min: 0,
        max: 1,
        notInRangeMessage: 'You must be between {{ min }}cm and {{ max }}cm tall to enter',
    )]
    #[QueryDbFieldName('command')]
    public int $cmd = 0;

    #[Assert\Choice(['New York', 'Berlin', 'Tokyo'])]
    public string $option = 'Berlin';

    public DaysOfWeek $weekDay = DaysOfWeek::Monday;
}

class StartWithQueryConditionStrategyTest extends TestCase
{
    public function testStartWithStrategyValidate()
    {
        $valInterface = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->addDefaultDoctrineAnnotationReader() // add this only when using annotations
            ->getValidator();

        $this->assertEquals('sw', StartWithQueryConditionStrategy::getOperator());

        $query = BasicFilterQuery::decodeRawQuery('option:sw:');
        $validator = new StartWithQueryConditionStrategy($query, TestValidClass::class);
        $errors = $validator->checkValid($valInterface);
        $this->assertCount(1, $errors);
        $this->assertEquals(Assert\Choice::NO_SUCH_CHOICE_ERROR, $errors[0]->getCode());

        $query = BasicFilterQuery::decodeRawQuery('option:sw');
        $validator = new StartWithQueryConditionStrategy($query, TestValidClass::class);
        $errors = $validator->checkValid($valInterface);
        $this->assertCount(1, $errors);
        $this->assertEquals(QueryConditionStrategy::EMPTY_VALUE, $errors[0]->getCode());

        $query = BasicFilterQuery::decodeRawQuery('mm:sw:1');
        $validator = new StartWithQueryConditionStrategy($query, TestValidClass::class);
        $errors = $validator->checkValid($valInterface);
        $this->assertCount(1, $errors);
        $this->assertEquals(QueryConditionStrategy::INVALID_FIELD, $errors[0]->getCode());

        $query = BasicFilterQuery::decodeRawQuery('cmd:sw:a,1');
        $validator = new StartWithQueryConditionStrategy($query, TestValidClass::class);
        $errors = $validator->checkValid($valInterface);
        $this->assertCount(1, $errors);
        $this->assertEquals(QueryConditionStrategy::TO_MANY_VALUES, $errors[0]->getCode());

        $query = BasicFilterQuery::decodeRawQuery('option:sw:a');
        $validator = new StartWithQueryConditionStrategy($query, TestValidClass::class);
        $errors = $validator->checkValid($valInterface);
        $this->assertCount(1, $errors);
        $this->assertEquals(Assert\Choice::NO_SUCH_CHOICE_ERROR, $errors[0]->getCode());

        $query = BasicFilterQuery::decodeRawQuery('option:sw:New York');
        $validator = new StartWithQueryConditionStrategy($query, TestValidClass::class);
        $errors = $validator->checkValid($valInterface);
        $this->assertCount(0, $errors);
    }

    public function testGenerateSqlConditionStatementWithDbFieldname()
    {
        $query = BasicFilterQuery::decodeRawQuery('cmd:sw:1');
        $validator = new StartWithQueryConditionStrategy($query, TestValidClass::class);
        $sqlSegment = $validator->generateSqlConditionStatement();
        $this->assertEquals('(command LIKE ?)', $sqlSegment);
    }

    public function testGenerateSqlConditionStatement()
    {
        $query = BasicFilterQuery::decodeRawQuery('option:sw:Berlin');
        $validator = new StartWithQueryConditionStrategy($query, TestValidClass::class);
        $sqlSegment = $validator->generateSqlConditionStatement();
        $this->assertEquals('(option LIKE ?)', $sqlSegment);
    }

    public function testInValueStrategyTypeCorrectValues()
    {
        $valInterface = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
                ->addDefaultDoctrineAnnotationReader() // add this only when using annotations
                    ->getValidator();

        // test integer
        $query = BasicFilterQuery::decodeRawQuery('cmd:sw:1');
        $strategy = new StartWithQueryConditionStrategy($query, TestValidClass::class);
        $errors = $strategy->checkValid($valInterface);
        $this->assertCount(0, $errors);

        $values = $strategy->generateSqlValues();
        $this->assertCount(1, $values);
        $this->assertEquals(1, $values[0]);

        // test string
        $query = BasicFilterQuery::decodeRawQuery('option:sw:Tokyo');
        $strategy = new StartWithQueryConditionStrategy($query, TestValidClass::class);
        $errors = $strategy->checkValid($valInterface);
        $this->assertCount(0, $errors);

        $values = $strategy->generateSqlValues();
        $this->assertCount(1, $values);
        $this->assertIsString($values[0]);
        $this->assertEquals('Tokyo%', $values[0]);

        // test enum
        $query = BasicFilterQuery::decodeRawQuery('weekDay:sw:Sunday');
        $strategy = new StartWithQueryConditionStrategy($query, TestValidClass::class);
        $errors = $strategy->checkValid($valInterface);
        $this->assertCount(0, $errors);

        $values = $strategy->generateSqlValues();
        $this->assertCount(1, $values);
        $this->assertEquals(DaysOfWeek::Sunday, $values[0]);
    }
}
