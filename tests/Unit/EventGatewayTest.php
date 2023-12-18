<?php

declare(strict_types=1);

namespace Tests\Unit;

use Codeception\Test\Unit;
use Faker\Factory;
use Faker\Generator;
use Foodsharing\Modules\Event\EventGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Tests\Support\UnitTester;

class EventGatewayTest extends Unit
{
    protected UnitTester $tester;
    private EventGateway $gateway;
    private Generator $faker;

    protected $foodsaver;
    protected $regionGateway;
    protected $region;
    protected $childRegion;

    public function _before()
    {
        $this->gateway = $this->tester->get(EventGateway::class);
        $this->faker = Factory::create('de_DE');

        $this->regionGateway = $this->tester->get(RegionGateway::class);
        $this->foodsaver = $this->tester->createFoodsaver();
        $this->region = $this->tester->createRegion('God');
        $this->tester->addRegionMember($this->region['id'], $this->foodsaver['id']);
        $this->childRegion = $this->tester->createRegion('Jesus', ['parent_id' => $this->region['id']]);
    }

    public function testAddLocation(): void
    {
        $name = $this->faker->company();
        $lat = $this->faker->latitude();
        $lon = $this->faker->longitude();
        $address = $this->faker->streetAddress();
        $zip = $this->faker->postcode();
        $city = $this->faker->city();
        $id = $this->gateway->addLocation($name, $lat, $lon, $address, $zip, $city);
        $this->assertGreaterThan(0, $id);
        $this->tester->seeInDatabase('fs_location', ['id' => $id, 'name' => $name, 'lat' => $lat, 'lon' => $lon, 'street' => $address, 'zip' => $zip, 'city' => $city]);
    }

    public function testAddEvent(): void
    {
        $event = [
            'bezirk_id' => $this->region['id'],
            'location_id' => null,
            'public' => 0,
            'name' => 'name',
            'start' => '2018-09-01 12:00',
            'end' => '2018-09-30 12:00',
            'description' => 'd',
            'bot' => 0,
            'online' => 0,
            'otherStuff' => 'that should not bother...'
        ];
        $id = $this->gateway->addEvent($this->foodsaver['id'], $event);
        $this->assertGreaterThan(0, $id);
        unset($event['otherStuff']);
        $event['foodsaver_id'] = $this->foodsaver['id'];
        $this->tester->seeInDatabase('fs_event', $event);
    }

    public function testInviteFullRegion(): void
    {
        $event = [
            'bezirk_id' => $this->region['id'],
            'location_id' => null,
            'public' => 0,
            'name' => 'name',
            'start' => '2018-09-01 12:00',
            'end' => '2018-09-30 12:00',
            'description' => 'd',
            'bot' => 0,
            'online' => 0,
        ];
        $eventid = $this->gateway->addEvent($this->foodsaver['id'], $event);

        $usersInRegion = [$this->foodsaver['id']];
        $fs = $this->tester->createFoodsaver();
        $this->tester->addRegionMember($this->region['id'], $fs['id']);
        $usersInRegion[] = $fs['id'];

        $this->gateway->inviteFullRegion($this->region['id'], $eventid, false);
        foreach ($usersInRegion as $fsid) {
            $this->tester->seeInDatabase('fs_foodsaver_has_event', ['foodsaver_id' => $fsid, 'event_id' => $eventid, 'status' => 0]);
        }

        $fs = $this->tester->createFoodsaver();
        $this->tester->addRegionMember($this->childRegion['id'], $fs['id']);
        $usersInRegion[] = $fs['id'];

        $this->gateway->inviteFullRegion($this->region['id'], $eventid, true);
        foreach ($usersInRegion as $fsid) {
            $this->tester->seeInDatabase('fs_foodsaver_has_event', ['foodsaver_id' => $fsid, 'event_id' => $eventid, 'status' => 0]);
        }
    }

    public function testListEvents(): void
    {
        $dateFormat = 'Y-m-d H:i';

        $dateMinusTwoHours = date($dateFormat, time() - (2 * 60 * 60));
        $dateMinusOneHour = date($dateFormat, time() - (60 * 60));
        $datePlusOneHour = date($dateFormat, time() + (60 * 60));
        $datePlusTwoHours = date($dateFormat, time() + (2 * 60 * 60));
        $events = [
            [
                'bezirk_id' => $this->region['id'],
                'location_id' => null,
                'public' => 0,
                'name' => 'EventInPast',
                'start' => $dateMinusTwoHours,
                'end' => $dateMinusOneHour,
                'description' => 'd',
                'bot' => 0,
                'online' => 0,
            ],
            [
                'bezirk_id' => $this->region['id'],
                'location_id' => null,
                'public' => 0,
                'name' => 'EventRunning',
                'start' => $dateMinusOneHour,
                'end' => $datePlusOneHour,
                'description' => 'd',
                'bot' => 0,
                'online' => 0,
            ],
            [
                'bezirk_id' => $this->region['id'],
                'location_id' => null,
                'public' => 0,
                'name' => 'EventInFuture',
                'start' => $datePlusOneHour,
                'end' => $datePlusTwoHours,
                'description' => 'd',
                'bot' => 0,
                'online' => 0,
            ],
        ];
        foreach ($events as $event) {
            $eventid = $this->gateway->addEvent($this->foodsaver['id'], $event);
            $this->assertGreaterThan(0, $eventid);
        }
        $listedEvents = $this->gateway->listForRegion($this->region['id']);

        $this->assertEquals(sizeof($events), sizeof($listedEvents), 'All events of a region should be listed');

        foreach ($events as $event) {
            $this->assertNotEmpty(array_filter($listedEvents, function ($listedEvent) use ($event) {
                return $listedEvent['name'] == $event['name'];
            }));
        }
    }
}
