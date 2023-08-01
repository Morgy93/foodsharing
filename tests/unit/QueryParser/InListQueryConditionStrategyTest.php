<?php

use Foodsharing\RestApi\Models\QueryParser\BasicFilterQuery;
use Foodsharing\RestApi\Models\QueryParser\InListQueryConditionStrategy;
use Foodsharing\RestApi\Models\QueryParser\QueryDbFieldName;
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

class InListQueryConditionStrategyTest extends TestCase
{
    public function testInValueStrategyValidate()
    {
        $valInterface = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
                ->addDefaultDoctrineAnnotationReader() // add this only when using annotations
                    ->getValidator();

        $this->assertEquals('in', InListQueryConditionStrategy::getOperator());

        $query = BasicFilterQuery::decodeRawQuery('mm:in:1');
        $validator = new InListQueryConditionStrategy($query, TestValidClass::class);
        $errors = $validator->checkValid($valInterface);
        $this->assertCount(1, $errors);

        $query = BasicFilterQuery::decodeRawQuery('cmd:in:a,1');
        $validator = new InListQueryConditionStrategy($query, TestValidClass::class);
        $errors = $validator->checkValid($valInterface);

        $this->assertCount(1, $errors);

        $query = BasicFilterQuery::decodeRawQuery('cmd:in:0,1');
        $validator = new InListQueryConditionStrategy($query, TestValidClass::class);
        $errors = $validator->checkValid($valInterface);

        $this->assertCount(0, $errors);
    }

    public function testGenerateSqlConditionStatementWithDbFieldname()
    {
        $query = BasicFilterQuery::decodeRawQuery('cmd:in:1');
        $validator = new InListQueryConditionStrategy($query, TestValidClass::class);
        $sqlSegment = $validator->generateSqlConditionStatement();
        $this->assertEquals('(command IN ( ? ))', $sqlSegment);

        $query = BasicFilterQuery::decodeRawQuery('cmd:in:1,2');
        $validator = new InListQueryConditionStrategy($query, TestValidClass::class);
        $sqlSegment = $validator->generateSqlConditionStatement();
        $this->assertEquals('(command IN ( ?, ? ))', $sqlSegment);
    }

    public function testGenerateSqlConditionStatement()
    {
        $query = BasicFilterQuery::decodeRawQuery('option:in:Berlin');
        $validator = new InListQueryConditionStrategy($query, TestValidClass::class);
        $sqlSegment = $validator->generateSqlConditionStatement();
        $this->assertEquals('(option IN ( ? ))', $sqlSegment);

        $query = BasicFilterQuery::decodeRawQuery('option:in:Berlin,Tokyo');
        $validator = new InListQueryConditionStrategy($query, TestValidClass::class);
        $sqlSegment = $validator->generateSqlConditionStatement();
        $this->assertEquals('(option IN ( ?, ? ))', $sqlSegment);
    }

    public function testInValueStrategyTypeCorrectValues()
    {
        $valInterface = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
                ->addDefaultDoctrineAnnotationReader() // add this only when using annotations
                    ->getValidator();

        // test integer
        $query = BasicFilterQuery::decodeRawQuery('cmd:in:1');
        $strategy = new InListQueryConditionStrategy($query, TestValidClass::class);
        $errors = $strategy->checkValid($valInterface);
        $this->assertCount(0, $errors);

        $values = $strategy->generateSqlValues();
        $this->assertCount(1, $values);
        $this->assertIsInt($values[0]);
        $this->assertEquals(1, $values[0]);

        // test string
        $query = BasicFilterQuery::decodeRawQuery('option:in:Tokyo');
        $strategy = new InListQueryConditionStrategy($query, TestValidClass::class);
        $errors = $strategy->checkValid($valInterface);
        $this->assertCount(0, $errors);

        $values = $strategy->generateSqlValues();
        $this->assertCount(1, $values);
        $this->assertIsString($values[0]);
        $this->assertEquals('Tokyo', $values[0]);

        // test enum
        $query = BasicFilterQuery::decodeRawQuery('weekDay:in:Sunday');
        $strategy = new InListQueryConditionStrategy($query, TestValidClass::class);
        $errors = $strategy->checkValid($valInterface);
        $this->assertCount(0, $errors);

        $values = $strategy->generateSqlValues();
        $this->assertCount(1, $values);
        $this->assertEquals(DaysOfWeek::Sunday, $values[0]);
    }
}
