<?php

namespace Foodsharing\RestApi\Models\User;

use OpenApi\Annotations as OA;

/**
 * Desribes foodsharing user and this information.
 *
 * @OA\Schema(required={"weight", "count"})
 */
class UserPermissionsModel
{
	/**
	 * Represent the permission to edit this profile (My user profil or admin user)
	 * Rated to ProfilePermissions.
	 *
	 * @OA\Property(format="boolean", example=false)
	 */
	public bool $mayEditUserProfile;

	/**
	 * @OA\Property(type="string", example="Govinda Natur GmbH")
	 */
	public bool $mayAdministrateUserProfile;

	/**
	 * @OA\Property(type="string", example="Govinda Natur GmbH")
	 */
	public bool $administrateBlog;

	/**
	 * @OA\Property(type="string", example="Govinda Natur GmbH")
	 */
	public bool $editQuiz;

	/**
	 * @OA\Property(type="string", example="Govinda Natur GmbH")
	 */
	public bool $handleReports;

	/**
	 * @OA\Property(type="string", example="Govinda Natur GmbH")
	 */
	public bool $addStore;

	/**
	 * @OA\Property(type="string", example="Govinda Natur GmbH")
	 */
	public bool $manageMailboxes;

	/**
	 * @OA\Property(type="string", example="Govinda Natur GmbH")
	 */
	public bool $editContent;

	/**
	 * @OA\Property(type="string", example="Govinda Natur GmbH")
	 */
	public bool $administrateNewsletterEmail;

	/**
	 * @OA\Property(type="string", example="Govinda Natur GmbH")
	 */
	public bool $administrateRegions;
}
