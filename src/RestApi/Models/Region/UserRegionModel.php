<?php

namespace Foodsharing\RestApi\Models\Region;

use OpenApi\Annotations as OA;

/**
 * Provides information about the region and groups of an user.
 *
 * @OA\Schema()
 */
class UserRegionModel
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
	 * @OA\Property(example="Sinsheim")
	 */
	public string $name = '';

	/**
	 * Kind of region.
	 *
	 * - 0: Region
	 * - 1: Group
	 *
	 * @OA\Property()
	 */
	public int $classification = 0;

	/**
	 * Is responsible user.
	 *
	 * - False: Normal member of region or Group
	 * - True: Is admin of group or ambassador
	 *
	 * @OA\Property()
	 */
	public bool $role = false;
}
