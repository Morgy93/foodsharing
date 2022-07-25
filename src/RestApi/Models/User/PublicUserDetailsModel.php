<?php

namespace Foodsharing\RestApi\Models\User;

use Foodsharing\RestApi\Models\Profile\PublicProfileModel;
use OpenApi\Annotations as OA;

/**
 * Describes user information which are public visible.
 *
 * @OA\Schema()
 */
class PublicUserDetailsModel extends PublicProfileModel
{
	/**
	 * UNCLEAR USAGE: API caller role in system  is the role foodsaver ("fs").
	 *
	 * @OA\Property(type="Boolean", example=true)
	 */
	public bool $foodsaver;

	/**
	 * The user is verified by a ambassador to a foodsharer. The status of verification is related to the home region.
	 * If the user change the home region then the verification status is lost.
	 *
	 * @OA\Property(type="Boolean", example=true)
	 */
	public bool $isVerified;

	/**
	 * Home region id of the user. The user have only one home region which.
	 *
	 * @OA\Property(type="int32", example=1)
	 */
	public int $regionId = 0;

	/**
	 * Home region name of the user. The user have only a home region of the foodsharing quiz is finished successful.
	 *
	 * @OA\Property(type="string", example="Hamburg", maxLength=120)
	 */
	public ?string $regionName = '';
}
