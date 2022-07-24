<?php

namespace Foodsharing\RestApi;

use Foodsharing\RestApi\Models\User\UserPermissionsModel;
use Foodsharing\RestApi\Models\User\UserStatisticsModel;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;

class CurrentUserProfileRestController extends AbstractFOSRestController
{
	public function __construct()
	{
	}

	/**
	 * Provides my user profile information.
	 *
	 * @OA\Tag(name="my")
	 *
	 * @Rest\Get("user/current/statistics")
	 * @OA\Response(
	 * 		response="200",
	 * 		description="Success.",
	 *      @Model(type=UserStatisticsModel::class)
	 * )
	 */
	public function getMyUserStats(): Response
	{
		return $this->handleView($this->view(new UserStatisticsModel(), 200));
	}

	/**
	 * Provides my user profile information.
	 *
	 * @OA\Tag(name="my")
	 *
	 * @Rest\Get("user/current/permissions")
	 * @OA\Response(
	 * 		response="200",
	 * 		description="Success.",
	 *      @Model(type=UserPermissionsModel::class)
	 * )
	 */
	public function getMyUserPermissions(): Response
	{
		return $this->handleView($this->view(new UserPermissionsModel(), 200));
	}
}
