<?php

namespace Foodsharing\RestApi\Models\User;

use OpenApi\Annotations as OA;

/**
 * Describes the management permissions of the user.
 *
 * @OA\Schema()
 */
class UserPermissionsModel
{
	/**
	 * Represent the permission to edit this profile (My user profil or admin user)
	 * Rated to ProfilePermissions.
	 *
	 * @OA\Property(example=false)
	 */
	public bool $mayEditUserProfile;

	/**
	 * @OA\Property(example=false)
	 */
	public bool $mayAdministrateUserProfile;

	/**
	 * @OA\Property(example=false)
	 */
	public bool $administrateBlog;

	/**
	 * @OA\Property(example=false)
	 */
	public bool $editQuiz;

	/**
	 * @OA\Property(example=false)
	 */
	public bool $handleReports;

	/**
	 * @OA\Property(example=false)
	 */
	public bool $addStore;

	/**
	 * @OA\Property(example=false)
	 */
	public bool $manageMailboxes;

	/**
	 * @OA\Property(example=false)
	 */
	public bool $editContent;

	/**
	 * @OA\Property(example=false)
	 */
	public bool $administrateNewsletterEmail;

	/**
	 * @OA\Property(example=false)
	 */
	public bool $administrateRegions;
}
