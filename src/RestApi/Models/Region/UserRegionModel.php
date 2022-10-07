<?php

namespace Foodsharing\RestApi\Models\Region;

use Foodsharing\Modules\Unit\DTO\UserUnit;
use OpenApi\Annotations as OA;

/**
 * Provides information about the region of an user.
 *
 * @OA\Schema()
 */
class UserRegionModel
{
	/**
	 *  Identifier of region.
	 *
	 * @OA\Property(example="1"))
	 */
	public int $id = 0;

	/**
	 * Name of region.
	 *
	 * @OA\Property(example="Sinsheim")
	 */
	public string $name = '';

	/**
	 * Kind of region.
	 *
	 * - 1: CITY
	 * - 2: DISTRICT
	 * - 3: REGION
	 * - 5: FEDERAL_STATE
	 * - 6: COUNTRY
	 * - 8: BIG_CITY
	 * - 9: PART_OF_TOWN
	 *
	 * @OA\Property()
	 */
	public int $classification = 0;

	/**
	 * Is responsible user.
	 *
	 * - False: Normal member
	 * - True: Is ambassador
	 *
	 * @OA\Property()
	 */
	public bool $isResponsible = false;

	public static function createFrom(UserUnit $UserUnit)
	{
		$obj = new UserRegionModel();
		$obj->id = $UserUnit->unit->id;
		$obj->name = $UserUnit->unit->name;
		$obj->classification = $UserUnit->unit->type;
		$obj->isResponsible = $UserUnit->isResponsible;

		return $obj;
	}
}
