<?php

use Carbon\Carbon;
use Codeception\Test\Unit;
use Faker\Factory;
use Faker\Generator;
use Foodsharing\Modules\Core\DBConstants\Store\CooperationStatus;
use Foodsharing\Modules\Core\DBConstants\StoreTeam\MembershipStatus;
use Foodsharing\Modules\Store\DTO\CreateStoreData;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Modules\Store\TeamStatus;

class StoreGatewayTest extends Unit
{
	protected UnitTester $tester;
	private Generator $faker;
	private StoreGateway $gateway;

	private array $store;
	private array $foodsaver;
	private array $region;

	/**
	 * @throws Exception
	 */
	private function storeData($status = 'none'): array
	{
		return [
			'id' => $this->store['id'],
			'name' => $this->store['name'],
			'region_name' => $this->region['name'],
			'betrieb_kategorie_id' => $this->store['betrieb_kategorie_id'],
			'kette_id' => $this->store['kette_id'],
			'betrieb_status_id' => $this->store['betrieb_status_id'],
			'ansprechpartner' => $this->store['ansprechpartner'],
			'fax' => $this->store['fax'],
			'telefon' => $this->store['telefon'],
			'email' => $this->store['email'],
			'geo' => implode(', ', [$this->store['lat'], $this->store['lon']]),
			'anschrift' => $this->store['str'],
			'str' => $this->store['str'],
			'plz' => $this->store['plz'],
			'stadt' => $this->store['stadt'],
			'added' => (new DateTime($this->store['added']))->format('Y-m-d'),
			'verantwortlich' => ($status === 'team') ? 0 : null,
			'active' => ($status === 'team') ? 1 : null,
		];
	}

	protected function _before(): void
	{
		$this->gateway = $this->tester->get(StoreGateway::class);
		$this->region = $this->tester->createRegion();
		$this->store = $this->tester->createStore($this->region['id']);
		$this->foodsaver = $this->tester->createFoodsaver();
		$this->faker = Factory::create('de_DE');
	}

	public function testAddNewStore(): void
	{
		$storeDTO = new CreateStoreData();
		$storeDTO->name = 'StoreGatewayTestbetrieb';
		$storeDTO->regionId = 1567;
		$storeDTO->lat = 51.5367827;
		$storeDTO->lon = 9.9258967;
		$storeDTO->str = 'Bahnhofsplatz 1';
		$storeDTO->zip = '37073';
		$storeDTO->city = 'Göttingen';
		$storeDTO->publicInfo = 'Testeintrag im Feld öffentliche Information';
		$storeDTO->createdAt = Carbon::now();
		$storeDTO->updatedAt = Carbon::now();

		$storeId = $this->gateway->addStore($storeDTO);

		$this->assertIsInt($storeId);
		$this->assertTrue($storeId !== 0);
	}

	public function testIsInTeam(): void
	{
		$this->assertEquals(
			TeamStatus::NoMember,
			$this->gateway->getUserTeamStatus($this->foodsaver['id'], $this->store['id'])
		);

		$this->tester->addStoreTeam($this->store['id'], $this->foodsaver['id']);
		$this->assertEquals(
			TeamStatus::Member,
			$this->gateway->getUserTeamStatus($this->foodsaver['id'], $this->store['id'])
		);

		$coordinator = $this->tester->createStoreCoordinator();
		$this->tester->addStoreTeam($this->store['id'], $coordinator['id'], true);
		$this->assertEquals(
			TeamStatus::Coordinator,
			$this->gateway->getUserTeamStatus($coordinator['id'], $this->store['id'])
		);

		$waiter = $this->tester->createFoodsaver();
		$this->tester->addStoreTeam($this->store['id'], $waiter['id'], false, true);
		$this->assertEquals(
			TeamStatus::WaitingList,
			$this->gateway->getUserTeamStatus($waiter['id'], $this->store['id'])
		);
	}

	/**
	 * @throws Exception
	 */
	public function testListStoresForFoodsaver(): void
	{
		$this->assertEquals(
			[
				'verantwortlich' => [],
				'team' => [],
				'waitspringer' => [],
				'requested' => [],
				'sonstige' => [$this->storeData()],
			],
			$this->gateway->getMyStores($this->foodsaver['id'], $this->region['id'])
		);

		$this->tester->addStoreTeam($this->store['id'], $this->foodsaver['id']);

		$this->assertEquals(
			[
				'verantwortlich' => [],
				'team' => [$this->storeData('team')],
				'waitspringer' => [],
				'requested' => [],
				'sonstige' => [],
			],
			$this->gateway->getMyStores($this->foodsaver['id'], $this->region['id'])
		);
	}

	public function testUpdateStoreRegion(): void
	{
		$newRegion = $this->tester->createRegion();

		$updates = $this->gateway->updateStoreRegion($this->store['id'], $newRegion['id']);

		$this->tester->seeInDatabase('fs_betrieb', ['bezirk_id' => $newRegion['id'], 'id' => $this->store['id']]);
	}

	public function testGetNoTeamConversation(): void
	{
		$conversationId = $this->gateway->getBetriebConversation($this->store['id']);

		$this->tester->assertEquals(0, $conversationId);
	}

	public function testGetNoSpringerConversation(): void
	{
		$conversationId = $this->gateway->getBetriebConversation($this->store['id'], true);

		$this->tester->assertEquals(0, $conversationId);
	}

	public function testFoodsaverRelatedStoreMembershipStatus(): void
	{
		$store1 = $this->tester->createStore(
			$this->region['id'], null, null, ['betrieb_status_id' => CooperationStatus::COOPERATION_ESTABLISHED]
		);
		$store2 = $this->tester->createStore(
			$this->region['id'], null, null, ['betrieb_status_id' => CooperationStatus::COOPERATION_ESTABLISHED]
		);
		$store3 = $this->tester->createStore(
			$this->region['id'], null, null, ['betrieb_status_id' => CooperationStatus::COOPERATION_ESTABLISHED]
		);
		$store4 = $this->tester->createStore(
			$this->region['id'], null, null, ['betrieb_status_id' => CooperationStatus::COOPERATION_ESTABLISHED]
		);
		$store5 = $this->tester->createStore(
			$this->region['id'], null, null, ['betrieb_status_id' => CooperationStatus::DOES_NOT_WANT_TO_WORK_WITH_US]
		);

		$this->tester->addStoreTeam($store1['id'], $this->foodsaver['id'], true, false, true); // Test coordinator
		$this->tester->addStoreTeam(
			$store2['id'], $this->foodsaver['id'], false, true, true
		); // Test waiting for membership (JUMPER) - Not shown
		$this->tester->addStoreTeam(
			$store3['id'], $this->foodsaver['id'], false, false, true
		); // Test membership (MEMBER)
		$this->tester->addStoreTeam(
			$store4['id'], $this->foodsaver['id'], false, false, false
		); // Test open request confirmed (Pending request) - Not shown
		$this->tester->addStoreTeam(
			$store5['id'], $this->foodsaver['id'], false, false, false
		); // Test open request confirmed (Pending request) - Not shown
		$this->tester->addStoreTeam(
			$store2['id'], $this->foodsaver['id'] + 1, false, false, true
		); // Test open request confirmed (Pending request)

		$expectation = [
			[
				'store_id' => $store1['id'], 'store_name' => $store1['name'], 'managing' => 1,
				'membership_status' => MembershipStatus::MEMBER,
			],
			[
				'store_id' => $store3['id'], 'store_name' => $store3['name'], 'managing' => 0,
				'membership_status' => MembershipStatus::MEMBER,
			],
			[
				'store_id' => $store2['id'], 'store_name' => $store2['name'], 'managing' => 0,
				'membership_status' => MembershipStatus::JUMPER,
			],
			[
				'store_id' => $store4['id'], 'store_name' => $store4['name'], 'managing' => 0,
				'membership_status' => MembershipStatus::APPLIED_FOR_TEAM,
			],
		];
		usort($expectation, function ($a, $b) {
			if ($a['managing'] == $b['managing']) {
				if ($a['membership_status'] == $b['membership_status']) {
					if ($a['store_id'] == $b['store_id']) {
						return 0;
					}
					if ($a['store_id'] < $b['store_id']) {
						return -1;
					} else {
						return 1;
					}
				}
				if ($a['membership_status'] < $b['membership_status']) {
					return -1;
				} else {
					return 1;
				}
			}
			if ($a['managing'] > $b['managing']) {
				return -1;
			} else {
				return 1;
			}
		});
		$result = $this->gateway->listAllStoreTeamMembershipsForFoodsaver(
			$this->foodsaver['id'], [
				CooperationStatus::UNCLEAR, CooperationStatus::NO_CONTACT, CooperationStatus::IN_NEGOTIATION,
				CooperationStatus::COOPERATION_STARTING, CooperationStatus::COOPERATION_ESTABLISHED,
				CooperationStatus::PERMANENTLY_CLOSED,
			]
		);
		$this->assertEquals(4, count($result));
		$this->assertEquals($expectation[0]['store_id'], $result[0]->store->id);
		$this->assertEquals($expectation[0]['store_name'], $result[0]->store->name);
		$this->assertEquals($expectation[0]['managing'], $result[0]->isManaging);
		$this->assertEquals($expectation[0]['membership_status'], $result[0]->membershipStatus);

		$this->assertEquals($expectation[1]['store_id'], $result[1]->store->id);
		$this->assertEquals($expectation[1]['store_name'], $result[1]->store->name);
		$this->assertEquals($expectation[1]['managing'], $result[1]->isManaging);
		$this->assertEquals($expectation[1]['membership_status'], $result[1]->membershipStatus);

		$this->assertEquals($expectation[2]['store_id'], $result[2]->store->id);
		$this->assertEquals($expectation[2]['store_name'], $result[2]->store->name);
		$this->assertEquals($expectation[2]['managing'], $result[2]->isManaging);
		$this->assertEquals($expectation[2]['membership_status'], $result[2]->membershipStatus);

		$this->assertEquals($expectation[3]['store_id'], $result[3]->store->id);
		$this->assertEquals($expectation[3]['store_name'], $result[3]->store->name);
		$this->assertEquals($expectation[3]['managing'], $result[3]->isManaging);
		$this->assertEquals($expectation[3]['membership_status'], $result[3]->membershipStatus);
	}

	public function testStoreCooperationFilterForFoodsaverRelatedStoreMembershipsByStatusNotWantToWorkWithUs(): void
	{
		$store2 = $this->tester->createStore(
			$this->region['id'], null, null, ['betrieb_status_id' => CooperationStatus::COOPERATION_ESTABLISHED]
		);
		$store5 = $this->tester->createStore(
			$this->region['id'], null, null, ['betrieb_status_id' => CooperationStatus::DOES_NOT_WANT_TO_WORK_WITH_US]
		);

		$this->tester->addStoreTeam(
			$store5['id'], $this->foodsaver['id'], false, false, false
		); // Test open request confirmed (Pending request) - Not shown
		$this->tester->addStoreTeam(
			$store2['id'], $this->foodsaver['id'] + 1, false, false, true
		); // Test open request confirmed (Pending request)
		$this->tester->addStoreTeam(
			$store5['id'], $this->foodsaver['id'] + 1, false, false, true
		); // Test open request confirmed (Pending request)

		$result = $this->gateway->listAllStoreTeamMembershipsForFoodsaver(
			$this->foodsaver['id'], [CooperationStatus::DOES_NOT_WANT_TO_WORK_WITH_US]
		);
		$this->assertEquals(1, count($result));
		$this->assertEquals($store5['id'], $result[0]->store->id);
		$this->assertEquals($store5['name'], $result[0]->store->name);
		$this->assertFalse($result[0]->isManaging);
		$this->assertEquals(MembershipStatus::APPLIED_FOR_TEAM, $result[0]->membershipStatus);
	}
}
