<?php

use Foodsharing\RestApi\Models\QueryParser\BasicFilterQuery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints as Assert;

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
    public int $cmd = 0;

    #[Assert\Choice(['New York', 'Berlin', 'Tokyo'])]
    public string $option = 'Berlin';

    public DaysOfWeek $weekDay = DaysOfWeek::Monday;
}

class QueryParamDbBuilderTest extends TestCase
{
    public function testBasicQueryFieldSeperation()
    {
        $result = BasicFilterQuery::decodeRawQuery('NoSeperator');
        $this->assertEquals('NoSeperator', $result->field);
        $this->assertEquals(null, $result->operator);
        $this->assertEquals([], $result->values);

        $result = BasicFilterQuery::decodeRawQuery('NoOperation:');
        $this->assertEquals('NoOperation', $result->field);
        $this->assertEquals(null, $result->operator);
        $this->assertEquals([], $result->values);

        $result = BasicFilterQuery::decodeRawQuery('');
        $this->assertEquals('', $result->field);
        $this->assertEquals(null, $result->operator);
        $this->assertEquals([], $result->values);
    }

      public function testOperatorDecoding()
      {
          $result = BasicFilterQuery::decodeRawQuery('field:set');
          $this->assertEquals('set', $result->operator);
          $this->assertEquals('field', $result->field);

          $result = BasicFilterQuery::decodeRawQuery('toLowerCase:SET');
          $this->assertEquals('set', $result->operator);

          $result = BasicFilterQuery::decodeRawQuery('missingOp:');
          $this->assertEquals(null, $result->operator);

          $result = BasicFilterQuery::decodeRawQuery('empty_op::');
          $this->assertEquals(null, $result->operator);
      }

      public function testValueDecoding()
      {
          $result = BasicFilterQuery::decodeRawQuery('field:op:value');
          $this->assertEquals(['value'], $result->values);

          // test segmentation by ,
          $result = BasicFilterQuery::decodeRawQuery('multiValue:in:va1,va2');
          $this->assertEquals(['va1', 'va2'], $result->values);

          $result = BasicFilterQuery::decodeRawQuery('sep:range:1:12');
          $this->assertEquals(['1', '12'], $result->values);
      }
}
