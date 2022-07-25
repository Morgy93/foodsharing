<?php

namespace Foodsharing\RestApi\Models\User;

use OpenApi\Annotations as OA;

/**
 * Describes user information which are public visible.
 *
 * @OA\Schema()
 */
class PublicUserModel
{
	/**
	 * The foodsharer identifier of user.
	 *
	 * @OA\Property(format="int32", example=1)
	 */
	public ?int $id;

	/**
	 * First name of the user.
	 *
	 * @OA\Property(example="Peter", maxLength=120)
	 */
	public string $firstname = '';

	/**
	 * Provides the sleep status of the user.
	 *
	 * 	- 0: NONE User is available for community
	 *  - 1: TEMP User is temporarly away (known timespace) and not accessable by community
	 *  - 2: FULL User is unkown away and not accessable by community
	 *
	 * @OA\Property(example=0)
	 */
	public int $sleepStatus = 0;
}
