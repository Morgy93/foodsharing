<?php

namespace Foodsharing\Modules\Store\DTO;

use DateTime;

class CreateStoreData
{
	public string $name;
	public int $regionId;

	public float $lat;
	public float $lon;
	public string $str;
	public string $hsnr = '';
	public string $zip;
	public string $city;

	public DateTime $createdAt;
	public DateTime $updatedAt;
}
