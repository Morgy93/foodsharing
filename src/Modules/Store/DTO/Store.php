<?php

namespace Foodsharing\Modules\Store\DTO;

use DateTime;

class Store
{
	public int $id;
	public string $name;
	public int $regionId;

	public float $lat;
	public float $lon;
	public string $str;
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
}
