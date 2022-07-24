<?php

namespace Foodsharing\RestApi\Models\User;

use Foodsharing\RestApi\Models\Profile\ProfileModel;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

/**
 * Describe user information for a user which is allowed to edit the user (for example user it self, ambassador for region).
 *
 * @OA\Schema()
 */
class EditableUserModel extends ProfileModel
{
	/**
	 * UNCLEAR USAGE: API caller role in system  is the role foodsaver ("fs").
	 *
	 * @OA\Property(example=true)
	 */
	public bool $foodsaver;

	/**
	 * The user is verified by a ambassador to a foodsharer. The status of verification is related to the home region.
	 * If the user change the home region then the verification status is lost.
	 *
	 * @OA\Property(example=true)
	 */
	public bool $isVerified;

	/**
	 * Home region id of the user. The user have only one home region which.
	 *
	 * @OA\Property(example=1)
	 */
	public ?int $regionId = null;

	/**
	 * Home region name of the user. The user have only a home region of the foodsharing quiz is finished successful.
	 *
	 * @OA\Property(example="Hamburg", maxLength=120)
	 */
	public ?string $regionName = '';

	/**
	 * Related mailbox of the user.
	 *
	 * @OA\Property(example=true)
	 */
	public ?string $mailboxId = '';

	/**
	 * @OA\Property(example=true)
	 */
	public ?string $hasCalendarToken = '';

	/**
	 * User is not available for community.
	 *
	 * @OA\Property(example=false)
	 */
	public ?bool $sleeping = false;

	/**
	 * @Model(type=UserStatisticsModel::class)
	 */
	public ?UserStatisticsModel $stats = null;

	/**
	 * @Model(type=UserPermissionsModel::class)
	 */
	public ?UserPermissionsModel $permissions = null;
}
