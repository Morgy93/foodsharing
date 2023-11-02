
<?php

use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Core\DBConstants\Unit\UnitType;
use Foodsharing\Modules\Search\SearchGateway;

class SearchGatewayTest extends \Codeception\Test\Unit
{
    protected UnitTester $tester;
    protected SearchGateway $gateway;
    protected array $regions;
    protected array $users;


    public function _before()
    {
        $this->gateway = $this->tester->get(SearchGateway::class);
        
        $regionEurope = $this->tester->createRegion('Europa', ['parent_id' => RegionIDs::ROOT, 'type' => UnitType::COUNTRY, 'has_children' => 1]);
        $regionCountry = $this->tester->createRegion('Deutschland', ['parent_id' => $regionEurope['id'], 'type' => UnitType::COUNTRY, 'has_children' => 1]);
        $regionState1 = $this->tester->createRegion('Sachsen', ['parent_id' => $regionCountry['id'], 'type' => UnitType::FEDERAL_STATE, 'has_children' => 1]);
        $regionState2 = $this->tester->createRegion('Sachsen-Anhalt', ['parent_id' => $regionCountry['id'], 'type' => UnitType::FEDERAL_STATE, 'has_children' => 1]);
        $regionCity1 = $this->tester->createRegion('Dresden', ['parent_id' => $regionState1['id'], 'type' => UnitType::CITY, 'has_children' => 1, 'email' => 'dreeesden']);
        $regionCity2 = $this->tester->createRegion('Freiberg', ['parent_id' => $regionState1['id'], 'type' => UnitType::CITY, 'has_children' => 1]);
        $regionCity3 = $this->tester->createRegion('Magdeburg', ['parent_id' => $regionState2['id'], 'type' => UnitType::CITY, 'has_children' => 1]);
        $regionCity4 = $this->tester->createRegion('Bad Dürrenberg', ['parent_id' => $regionState2['id'], 'type' => UnitType::CITY, 'has_children' => 1]);
        
        $this->regions = [
            'europe' => $regionEurope,
            'country' => $regionCountry,
            'state1' => $regionState1,
            'state2' => $regionState2,
            'city1' => $regionCity1,
            'city2' => $regionCity2,
            'city3' => $regionCity3,
            'city4' => $regionCity4,
        ];

        $this->groups = [
            'wg-city1' => $this->tester->createRegion('AG Dresden', ['parent_id' => $regionCity1['id'], 'type' => UnitType::WORKING_GROUP, 'email' => 'ag-mail-dresden']),
            'wg-city2' => $this->tester->createRegion('AG Freiberg', ['parent_id' => $regionCity2['id'], 'type' => UnitType::WORKING_GROUP, 'email' => 'ag-mail-freiberg']),
        ];

        $this->users = array_combine(
            array_map(fn($key) => 'user-' . $key, array_keys($this->regions)),
            array_map(fn($region) => 
                $this->tester->createFoodsaver(null, [
                    'name' => 'Nutzer aus ' . $region['name'],
                    'nachname' => 'Nachname',
                    'bezirk_id' => $region['id'],
                ]), $this->regions)
        );
    }

    private function assertCorrectSearchResult($variableName, $expectedElements, $searchResult)
    {
        $this->assertEqualsCanonicalizing(
            array_map(fn($key) => $this->$variableName[$key]['id'], $expectedElements),
            array_map(fn($searchResultObj) => $searchResultObj->id, $searchResult)
        );
    }

    public function testSearchRegions()
    {
        // Basic example:
        $this->assertCorrectSearchResult('regions', ['state1', 'state2'], $this->gateway->searchRegions('Sachsen', 1));
        
        // Not only word start:
        $this->assertCorrectSearchResult('regions', ['city2', 'city4'], $this->gateway->searchRegions('berg', 1));
        
        // cAsE dOsN't MaTtEr:
        $this->assertCorrectSearchResult('regions', ['city1'], $this->gateway->searchRegions('dRESDEN', 1));

        // Searching for mail adress:
        $this->assertCorrectSearchResult('regions', ['city1'], $this->gateway->searchRegions('dreeesden', 1));
    }

    public function testSearchWorkingGroups()
    {
        // Only find wgs in own regions
        $this->assertCorrectSearchResult('groups', ['wg-city1'], $this->gateway->searchWorkingGroups('ag', $this->users['user-city1']['id'], false));

        // Searching for mail adress:
        $this->assertCorrectSearchResult('groups', ['wg-city1'], $this->gateway->searchWorkingGroups('ag-mail', $this->users['user-city1']['id'], false));

        // except if searching globally:
        $this->assertCorrectSearchResult('groups', ['wg-city1', 'wg-city2'], $this->gateway->searchWorkingGroups('ag', $this->users['user-city1']['id'], true));
    }

    // public function testSearchUserInGroups()
    // {
    //     $region1 = $this->tester->createRegion();
    //     $region2 = $this->tester->createRegion();

    //     $fs1 = $this->tester->createFoodsaver(null, ['name' => 'Alberto', 'nachname' => 'Albertino']);
    //     $fs2 = $this->tester->createFoodsaver(null, ['name' => 'Albert', 'nachname' => 'Hunne']);
    //     $fs3 = $this->tester->createFoodsaver(null, ['name' => 'Fred', 'nachname' => 'Weiß']);
    //     $fs4 = $this->tester->createFoodsaver(null, ['name' => 'Karl-Heinz', 'nachname' => 'Liebermensch']);
    //     $fs5 = $this->tester->createFoodsaver(null, ['name' => 'Matthias (Matze)', 'nachname' => 'Altenburg von um Heuschreckenland']);

    //     $this->tester->addRegionMember($region1['id'], $fs4['id']);
    //     $this->tester->addRegionMember($region2['id'], $fs2['id']);
    //     $this->tester->addRegionMember($region2['id'], $fs3['id']);

    //     $f1 = $fs1['id'];
    //     $f2 = $fs2['id'];
    //     $f3 = $fs3['id'];
    //     $f4 = $fs4['id'];
    //     $f5 = $fs5['id'];

    //     $this->assertEqualsCanonicalizing(
    //         [$f1, $f2],
    //         array_column($this->searchGateway->searchUserInGroups('Albe', [], null), 'id')
    //     );
    //     $this->assertEqualsCanonicalizing(
    //         [$f4],
    //         array_column($this->searchGateway->searchUserInGroups('Karl-Heinz', [], null), 'id')
    //     );
    //     $this->assertEqualsCanonicalizing(
    //         [$f5],
    //         array_column($this->searchGateway->searchUserInGroups('-(Matze)', [], null), 'id')
    //     );
    //     $this->assertEqualsCanonicalizing(
    //         [$f5],
    //         array_column($this->searchGateway->searchUserInGroups('von Heuschreckenland', [], null), 'id')
    //     );
    //     $this->assertEqualsCanonicalizing(
    //         [$f5],
    //         array_column($this->searchGateway->searchUserInGroups('um Heuschreckenland', [], null), 'id')
    //     );
    //     $this->assertEqualsCanonicalizing(
    //         [],
    //         array_column($this->searchGateway->searchUserInGroups('Fr*d', [], null), 'id')
    //     );
    //     $this->assertEqualsCanonicalizing(
    //         [$f2],
    //         array_column($this->searchGateway->searchUserInGroups('Alb', [], [$region2['id']]), 'id')
    //     );
    // }
}