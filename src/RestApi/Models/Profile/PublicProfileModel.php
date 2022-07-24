<?php

namespace Foodsharing\RestApi\Models\Profile;

use OpenApi\Annotations as OA;

/**
 *  Describes user profile information which are public visible.
 *
 * @OA\Schema(required={"id", "aboutMePublic"})
 */
class PublicProfileModel
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
	 * @OA\Property(example="P", maxLength=1)
	 */
	public ?string $firstname = '';

	/**
	 * Public text to describe the user by it self.
	 *
	 * @OA\Property(example=true, maxLength=16777215)
	 */
	public string $aboutMePublic = '';
}
