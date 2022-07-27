<?php

namespace Foodsharing\RestApi\Models\Group;

use Foodsharing\Modules\Region\DTO\UserUnit;
use OpenApi\Annotations as OA;

/**
 * Provides information about the region and groups of an user.
 *
 * @OA\Schema()
 */
class UserGroupModel
{
	/**
	 *  Identifier of region or group.
	 *
	 * @OA\Property(example="1"))
	 */
	public int $id = 0;

	/**
	 * Name of region or group.
	 *
	 * @OA\Property(example="Öffentlichkeitsarbeit - Startseite")
	 */
	public string $name = '';

	/**
	 * Is responsible user.
	 *
	 * - False: Normal member
	 * - True: Is admin of group
	 *
	 * @OA\Property()
	 */
	public bool $isResponsible = false;

	public static function createFrom(UserUnit $UserUnit)
	{
		$obj = new UserGroupModel();
		$obj->id = $UserUnit->region->id;
		$obj->name = $UserUnit->region->name;
		$obj->isResponsible = $UserUnit->isResponsible;

		return $obj;
	}
}
