<?php

namespace Foodsharing\Modules\Store\DTO;

use OpenApi\Annotations as OA;

/**
 * Describes the common store metadata.
 */
class CommonLabel
{
	/**
	 * Maximum count of slots per pickup.
	 *
	 * The count of slots are limited by the foodsharing platform.
	 *
	 * @OA\Property(format="int64", example=1)
	 */
	public int $id = 0;

	public string $name = '';

	public static function createFromArray(array $data): CommonLabel
	{
		$obj = new CommonLabel();
		$obj->id = $data['id'];
		$obj->name = $data['name'];

		return $obj;
	}
}
