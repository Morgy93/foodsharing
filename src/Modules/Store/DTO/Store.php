<?php

namespace Foodsharing\Modules\Store\DTO;

/* use DateTime;

class Store
{
	public int $id;
	public string $name;
	public int $regionId;

	public float $lat;
	public float $lon;
	public string $str;
	public string $hsnr = '';
	public string $zip;
	public string $city;

	public string $publicInfo;
	public int $publicTime;

	public int $categoryId;
	public int $chainId;
	public int $cooperationStatus;

	public string $description;
	// public array $foodTypes; // specialcased in StoreTransaction

	public string $contactName;
	public string $contactPhone;
	public string $contactFax;
	public string $contactEmail;
	public ?DateTime $cooperationStart;

	public int $calendarInterval;
	public int $weight;
	public int $effort;
	public int $publicity;
	public int $sticker;

	public DateTime $updatedAt;
}*/

/**
 * (TODO) Represents data of one store, with several German database column names mapped to English.
 */
class Store
{
	private const COLUMNS_IN_DATABASE = [
		'id',
		'betrieb_status_id',
		'bezirk_id',
		'added',
		'plz',
		'stadt',
		'lat',
		'lon',
		'kette_id',
		'betrieb_kategorie_id',
		'name',
		'str',
		'hsnr',
		'status_date',
		'status',
		'ansprechpartner',
		'telefon',
		'fax',
		'email',
		'begin',
		'besonderheiten',
		'public_info',
		'public_time',
		'ueberzeugungsarbeit',
		'presse',
		'sticker',
		'abholmenge',
		'team_status',
		'prefetchtime',
		'team_conversation_id',
		'springer_conversation_id',
		'deleted_at',
		'lebensmittel', // special-cased handling in StoreRestController
	];

	public static function isValidStoreField(string $field)
	{
		return in_array($field, self::COLUMNS_IN_DATABASE);
	}

	public static function isEmptyable(string $field)
	{
		return !in_array($field, [
			'id',
			'bezirk_id',
			'added',
			'name',
			'besonderheiten',
		]);
	}

	public static function isNullable(string $field)
	{
		return in_array($field, [
			'presse',
			'sticker',
		]);
	}
}
