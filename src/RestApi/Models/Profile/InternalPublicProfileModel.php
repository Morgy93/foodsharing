<?php

namespace Foodsharing\RestApi\Models\Profile;

use OpenApi\Annotations as OA;

/**
 * Describes user profile information which are shared with members of the community.
 *
 * @OA\Schema()
 */
class InternalPublicProfileModel extends PublicProfileModel
{
	/**
	 * First name of the user.
	 *
	 * @OA\Property(example="Peter", maxLength=120)
	 */
	public ?string $firstname = '';

	/**
	 * Last name of the user.
	 *
	 * @OA\Property(example="Musterman", maxLength=120)
	 */
	public ?string $lastname = '';

	/**
	 * Homepage of the user.
	 *
	 * @OA\Property(example="https://www.foodsharing.de")
	 */
	public ?string $homepage = '';

	/**
	 * Path to user picture.
	 *
	 * @OA\Property(format="uri",example="/upload/d87ce740-0985-11ed-861d-0242ac120002", maxLength=50)
	 */
	public ?string $photo = '';

	/**
	 * NO FIELD IN PROFILE SETTINGS: Gender of the user.
	 *
	 *  - 0: NOT_SELECTED
	 *  - 1: MALE
	 *  - 2: FEMALE
	 *  - 3: DIVERSE
	 *
	 * @OA\Property(enum={0, 1, 2, 3})
	 */
	public int $gender = 0;
}
