<?php

use Foodsharing\RestApi\Models\QueryParser\InListQueryConditionStrategy;
use Foodsharing\RestApi\Models\QueryParser\QueryStrategyBuilder;
use Foodsharing\RestApi\Models\QueryParser\StartWithQueryConditionStrategy;
use Foodsharing\RestApi\Models\QueryParser\SupportedQueryConditionStrategy;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

class TestValidClassQSB
{
    #[Assert\Range(
        min: 0,
        max: 1,
        notInRangeMessage: 'You must be between {{ min }}cm and {{ max }}cm tall to enter',
    )]
    #[SupportedQueryConditionStrategy([InListQueryConditionStrategy::class])]
    public int $cmd = 0;

    #[Assert\Choice(['New York', 'Berlin', 'Tokyo'])]
    #[SupportedQueryConditionStrategy([InListQueryConditionStrategy::class, StartWithQueryConditionStrategy::class])]
    public string $option = 'Berlin';
}

class QueryStrategyBuilderTest extends \Codeception\Test\Unit
{
    public function testValidation()
    {
        // Test successful
        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->addDefaultDoctrineAnnotationReader() // add this only when using annotations
            ->getValidator();

        $collection = new QueryStrategyBuilder(TestValidClassQSB::class);
        $errors = $collection->validate($validator, ['option:in:Berlin']);
        $this->assertCount(0, $errors);

        // Test related operator
        $errors = $collection->validate($validator, ['option:sw:Berlin']);
        $this->assertCount(0, $errors);

        // Test invalid value
        $errors = $collection->validate($validator, ['option:in:Ostern']);
        $this->assertCount(1, $errors);
        $this->assertEquals('Ostern', $errors[0]->getInvalidValue());
        $this->assertEquals('8e179f1b-97aa-4560-a02f-2a8b42e49df7', $errors[0]->getCode());

        // Test invalid operator
        $errors = $collection->validate($validator, ['option:cmd:Ostern']);
        $this->assertCount(1, $errors);
        $this->assertEquals(['option:cmd:Ostern'], $errors[0]->getInvalidValue());
        $this->assertEquals(QueryStrategyBuilder::NO_STRATEGY_FOUND, $errors[0]->getCode());

        // Test not related operator
        $errors = $collection->validate($validator, ['weekDay:sw:Ostern']);
        $this->assertCount(1, $errors);
        $this->assertEquals(['weekDay:sw:Ostern'], $errors[0]->getInvalidValue());
        $this->assertEquals(QueryStrategyBuilder::NO_STRATEGY_FOUND, $errors[0]->getCode());

        // Test invalid field
        $errors = $collection->validate($validator, ['invalid:in:Ostern']);
        $this->assertCount(1, $errors);
        $this->assertEquals(['invalid:in:Ostern'], $errors[0]->getInvalidValue());
        $this->assertEquals(QueryStrategyBuilder::NO_STRATEGY_FOUND, $errors[0]->getCode());
    }

    public function testTranslationToStrategies()
    {
        // Test successful
        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->addDefaultDoctrineAnnotationReader() // add this only when using annotations
            ->getValidator();

        $rawQueries = ['option:in:Berlin'];
        $collection = new QueryStrategyBuilder(TestValidClassQSB::class);
        $errors = $collection->validate($validator, $rawQueries);
        $this->assertCount(0, $errors);

        $queries = $collection->findQueryConditionStrategies($rawQueries);
        $this->assertCount(1, $queries);
        $this->assertEquals(InListQueryConditionStrategy::class, get_class($queries[0]));
        $this->assertEquals(['Berlin'], $queries[0]->generateSqlValues());
    }
}
