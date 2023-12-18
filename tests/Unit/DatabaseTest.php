<?php

declare(strict_types=1);

namespace Tests\Unit;

use Codeception\Test\Unit;
use Exception;
use Foodsharing\Modules\Core\Database;
use ReflectionClass;
use Tests\Support\UnitTester;

class DatabaseTest extends Unit
{
    protected UnitTester $tester;
    private Database $db;

    final public function _before(): void
    {
        $this->db = $this->tester->get(Database::class);
        $this->foodsaver = $this->tester->createFoodsaver();
        $this->foodsaver2 = $this->tester->createFoodsaver();
    }

    final public function testFetchByCriteria(): void
    {
        $result = $this->db->fetchByCriteria(
            'fs_foodsaver',
            ['email', 'name'],
            ['id' => $this->foodsaver['id']]
        );
        $this->tester->assertEquals(
            $result,
            ['email' => $this->foodsaver['email'], 'name' => $this->foodsaver['name']]
        );
    }

    final public function testFetchAllByCriteria(): void
    {
        $result = $this->db->fetchAllByCriteria(
            'fs_foodsaver',
            ['email'],
            ['id' => $this->foodsaver['id']]
        );
        $this->tester->assertEquals(
            $result,
            [['email' => $this->foodsaver['email']]]
        );
    }

    final public function testFetchAllValuesByCriteria(): void
    {
        $result = $this->db->fetchAllValuesByCriteria(
            'fs_foodsaver',
            'email',
            ['id' => $this->foodsaver['id']]
        );
        $this->tester->assertEquals(
            $result,
            [$this->foodsaver['email']]
        );
    }

    final public function testFetchValueByCriteria(): void
    {
        $result = $this->db->fetchValueByCriteria(
            'fs_foodsaver',
            'email',
            ['id' => $this->foodsaver['id']]
        );
        $this->tester->assertEquals(
            $result,
            $this->foodsaver['email']
        );
    }

    final public function testFetchValueByCriteriaThrowsIfNotFound(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Expected one or more results, but none was returned');
        $this->db->fetchValueByCriteria(
            'fs_foodsaver',
            'email',
            ['id' => -1]
        );
    }

    final public function testInsertOrUpdate(): void
    {
        // update
        $data = [
            'id' => $this->foodsaver['id'],
            'name' => 'my new name'
        ];

        $this->tester->dontSeeInDatabase('fs_foodsaver', $data);
        $this->db->insertOrUpdate('fs_foodsaver', $data);
        $this->tester->seeInDatabase('fs_foodsaver', $data);

        // insert
        $data = [
            'id' => $this->foodsaver['id'] + 1000,
            'name' => 'my new name'
        ];

        $this->tester->dontSeeInDatabase('fs_foodsaver', $data);
        $this->db->insertOrUpdate('fs_foodsaver', $data);
        $this->tester->seeInDatabase('fs_foodsaver', $data);
    }

    final public function testDelete(): void
    {
        $params = ['quiz_id' => 1, 'foodsaver_id' => $this->foodsaver['id']];
        $this->tester->haveInDatabase('fs_quiz_session', $params);

        $this->db->delete('fs_quiz_session', $params);

        $this->tester->dontSeeInDatabase('fs_quiz_session', $params);
    }

    final public function testDeleteWithLimit(): void
    {
        $params = ['foodsaver_id' => $this->foodsaver['id']];
        $this->tester->haveInDatabase('fs_quiz_session', $params);
        $this->tester->haveInDatabase('fs_quiz_session', $params);
        $this->assertEquals(3, $this->db->count('fs_quiz_session', $params));

        $delCount = $this->db->delete('fs_quiz_session', $params, 2);

        $this->assertEquals(2, $delCount);
        $this->assertEquals(1, $this->db->count('fs_quiz_session', $params));
    }

    final public function testFlattenArray(): void
    {
        // use reflection to access private function
        $class = new ReflectionClass($this->db);
        $method = $class->getMethod('flattenArray');

        // without keys
        $this->assertEquals($method->invokeArgs($this->db, [['a', ['b', 'c'], 'd'], false]), ['a', 'b', 'c', 'd']);
        $this->assertEquals($method->invokeArgs($this->db, [['a', ['a', 'c'], 'd'], false]), ['a', 'a', 'c', 'd']);
        $this->assertEquals($method->invokeArgs($this->db, [[['a', 'b'], ['c', 'd']], false]), ['a', 'b', 'c', 'd']);
        $this->assertEquals($method->invokeArgs($this->db, [[['a', 'b'], ['a', 'd']], false]), ['a', 'b', 'a', 'd']);
        $this->assertEquals($method->invokeArgs($this->db, [['A' => 'a', ['B' => 'b', 'C' => 'c'], 'D' => 'd'], false]), ['a', 'b', 'c', 'd']);
        $this->assertEquals($method->invokeArgs($this->db, [['A' => 'a', ['A' => 'b', 'C' => 'c'], 'D' => 'd'], false]), ['a', 'b', 'c', 'd']);
        $this->assertEquals($method->invokeArgs($this->db, [[['A' => 'a', 'B' => 'b'], ['C' => 'c', 'D' => 'd']], false]), ['a', 'b', 'c', 'd']);
        $this->assertEquals($method->invokeArgs($this->db, [[['A' => 'a', 'B' => 'b'], ['A' => 'c', 'D' => 'd']], false]), ['a', 'b', 'c', 'd']);

        // with keys
        $this->assertEquals($method->invokeArgs($this->db, [['A' => 'a', ['B' => 'b', 'C' => 'c'], 'D' => 'd']]),
            ['A' => 'a', 'B' => 'b', 'C' => 'c', 'D' => 'd']);
        $this->assertEquals($method->invokeArgs($this->db, [['A' => 'a', ['A' => 'b', 'C' => 'c'], 'D' => 'd']]),
            ['A' => 'b', 'C' => 'c', 'D' => 'd']);
        $this->assertEquals($method->invokeArgs($this->db, [[['A' => 'a', 'B' => 'b'], ['C' => 'c', 'D' => 'd']]]),
            ['A' => 'a', 'B' => 'b', 'C' => 'c', 'D' => 'd']);
        $this->assertEquals($method->invokeArgs($this->db, [[['A' => 'a', 'B' => 'b'], ['A' => 'c', 'D' => 'd']]]),
            ['A' => 'c', 'B' => 'b', 'D' => 'd']);
    }

    final public function testInsertMultiple(): void
    {
        $fs1 = $this->tester->createFoodsaver();
        $fs2 = $this->tester->createFoodsaver();
        $fs3 = $this->tester->createFoodsaver();
        $conv = $this->tester->createConversation([]);

        $numRows = $this->db->insertMultiple('fs_foodsaver_has_conversation', [['conversation_id' => $conv['id'], 'foodsaver_id' => $fs1['id']], ['conversation_id' => $conv['id'], 'foodsaver_id' => $fs2['id']]]);
        $this->tester->assertSame($numRows, 2);
        $this->tester->seeNumRecords(2, 'fs_foodsaver_has_conversation', ['conversation_id' => $conv['id']]);
    }
}
