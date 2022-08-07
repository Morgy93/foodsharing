<?php

namespace Foodsharing\Modules\Foodsaver\DTO;

use Foodsharing\Modules\Core\DTO\Address;
use Foodsharing\Modules\Core\DTO\GeoCoordinate;

class EditableProfileDTO
{
	public int $id = 0;
	public string $firstname = '';
	public string $aboutMePublic = '';
	public string $lastname = '';
	public string $homepage = '';
	public string $photo = '';
	public string $gender = '';
	public string $email = '';
	public string $birthday = '';
	public string $mobile = '';
	public string $phone = '';
	public Address $location;
	public GeoCoordinate $coordinate;
	public string $aboutMeInternal = '';

	public function __construct()
	{
		$this->location = new Address();
		$this->coordinate = new GeoCoordinate();
	}

	public static function createFromArray($query_result, $prefix = ''): EditableProfileDTO
	{
		$obj = new EditableProfileDTO();
		$obj->id = $query_result["{$prefix}id"];
		$obj->firstname = $query_result["{$prefix}name"];
		$obj->aboutMePublic = $query_result["{$prefix}about_me_public"];
		$obj->lastname = $query_result["{$prefix}nachname"];
		$obj->homepage = $query_result["{$prefix}homepage"];
		$obj->photo = $query_result["{$prefix}photo"];
		$obj->gender = $query_result["{$prefix}geschlecht"];
		$obj->email = $query_result["{$prefix}email"];
		$obj->birthday = $query_result["{$prefix}geb_datum"];
		$obj->mobile = $query_result["{$prefix}handy"];
		$obj->phone = $query_result["{$prefix}telefon"];
		$obj->coordinate = GeoCoordinate::createFromArray($query_result, $prefix = '');
		$obj->location = Address::createFromArray($query_result, $prefix = '');
		$obj->aboutMeInternal = $query_result["{$prefix}aboutMeInternal"];

		return $obj;
	}
}
