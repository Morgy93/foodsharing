<?php

namespace Foodsharing\RestApi\Models\Profile;

use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

/**
 * Describes all user profile information.
 *
 * @OA\Schema(required={"id", "loggedIn", "foodsaver", "isVerified"})
 */
class ProfileModel extends InternalPublicProfileModel
{
	/**
	 * NO FIELD IN PROFILE SETTINGS but menu
	 * E-Mail address of the user which is used for user account verification newsletter subscribtions.
	 *
	 * @OA\Property(type="email", example="no-response@foodsharing.de", maxLength=120)
	 */
	public ?string $email = '';

	/**
	 * Birthday of the user.
	 *
	 * @OA\Property(format="date", example="1983-04-15")
	 */
	public ?string $birthday = '';

	/**
	 * Mobile contact number to call user.
	 *
	 * @OA\Property(example="+49 179 12345678", maxLength=50)
	 */
	public ?string $mobile = '';

	/**
	 *  Contact number to call user.
	 *
	 * @OA\Property(example="+49 30 123456789", maxLength=50)
	 */
	public ?string $landline = '';

	/**
	 * Living home position of user.
	 *
	 * @Model(type=CoordinatesModel::class)
	 */
	public ?CoordinatesModel $coordinates = null;

	/**
	 * Living address of user.
	 *
	 * @OA\Property(example=true, maxLength=120)
	 */
	public ?string $address = '';

	/**
	 * Living city of user.
	 *
	 * @OA\Property(example="Berlin", maxLength=100)
	 */
	public ?string $city = '';

	/**
	 * Living address zip code of user.
	 *
	 * @OA\Property(example="10115", maxLength=10)
	 */
	public ?string $postcode = '';

	/**
	 * Foodsharing internal text to describe the user by it self.
	 *
	 * @OA\Property(example="", maxLength=16777215)
	 */
	public string $aboutMeIntern = '';
}
