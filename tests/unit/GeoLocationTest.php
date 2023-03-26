<?php

use Foodsharing\Modules\Core\DTO\GeoLocation;

class GeoLocationTest extends \Codeception\Test\Unit
{
    public function testLoadOfGeoLocationWithFloat()
    {
        $dbResult = ['lon' => 49.921, 'lat' => 5.400];

        $location = GeoLocation::createFromArray($dbResult);

        $this->assertEquals(49.921, $location->lon);
        $this->assertEquals(5.400, $location->lat);
    }

    public function testLoadOfGeoLocationWithFloatAsString()
    {
        $dbResult = ['lon' => '49.921', 'lat' => '5.400'];
        $location = GeoLocation::createFromArray($dbResult);
        $this->assertEquals(49.921, $location->lon);
        $this->assertEquals(5.400, $location->lat);
    }

    public function testLoadOfGeoLocationWithInvalidAsStringThrowException()
    {
        try {
            $dbResult = ['lon' => null, 'lat' => null];
            GeoLocation::createFromArray($dbResult);
            $this->assertTrue(false, 'InvalidArgumentException not arrived');
        } catch (InvalidArgumentException $ex) {
            $this->assertEquals($ex->getMessage(), 'Longitude/Latitude is invalid.');
        }

        try {
            $dbResult = ['lon' => '', 'lat' => ''];
            GeoLocation::createFromArray($dbResult);
            $this->assertTrue(false, 'InvalidArgumentException not arrived');
        } catch (InvalidArgumentException $ex) {
            $this->assertEquals($ex->getMessage(), 'Longitude/Latitude is invalid.');
        }

        try {
            $dbResult = ['lon' => 'abc', 'lat' => '-w'];
            GeoLocation::createFromArray($dbResult);
            $this->assertTrue(false, 'InvalidArgumentException not arrived');
        } catch (InvalidArgumentException $ex) {
            $this->assertEquals($ex->getMessage(), 'Longitude/Latitude is invalid.');
        }

        try {
            $dbResult = ['lon' => null, 'lat' => null];
            GeoLocation::createFromArray($dbResult);
            $this->assertTrue(false, 'InvalidArgumentException not arrived');
        } catch (InvalidArgumentException $ex) {
            $this->assertEquals($ex->getMessage(), 'Longitude/Latitude is invalid.');
        }

        try {
            $dbResult = ['lon' => '49.921N', 'lat' => '5.400E'];
            GeoLocation::createFromArray($dbResult);
            $this->assertTrue(false, 'InvalidArgumentException not arrived');
        } catch (InvalidArgumentException $ex) {
            $this->assertEquals($ex->getMessage(), 'Longitude/Latitude is invalid.');
        }

        try {
            $dbResult = ['lon' => 'N49.921', 'lat' => 'E5.400'];
            GeoLocation::createFromArray($dbResult);
            $this->assertTrue(false, 'InvalidArgumentException not arrived');
        } catch (InvalidArgumentException $ex) {
            $this->assertEquals($ex->getMessage(), 'Longitude/Latitude is invalid.');
        }
    }
}
