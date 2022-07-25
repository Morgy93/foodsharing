<?php

namespace Foodsharing\RestApi;

use Foodsharing\RestApi\Models\Profile\ProfileModel;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;

class UserProfileRestController extends AbstractFOSRestController
{
	public function __construct()
	{
	}

	/**
	 * DRAFT: Provides the user profile information for an user.
	 *
	 * @OA\Tag(name="user")
	 *
	 * @Rest\Get("user/{id}/profile", requirements={"id" = "\d+"})
	 * @OA\Response(
	 * 		response="200",
	 * 		description="Success.",
	 *      @Model(type=ProfileModel::class)
	 * )
	 */
	public function getUserProfile(int $id): Response
	{
		return $this->handleView($this->view(new ProfileModel(), 200));
	}

	/**
	 * DRAFT: Provides my user profile information.
	 *
	 * @OA\Tag(name="my")
	 *
	 * @Rest\Get("user/current/profile", requirements={"id" = "\d+"})
	 * @OA\Response(
	 * 		response="200",
	 * 		description="Success.",
	 *      @Model(type=ProfileModel::class)
	 * )
	 */
	public function getMyUserProfile(): Response
	{
		return $this->handleView($this->view(new ProfileModel(), 200));
	}

	/**
	 * DRAFT: Modifies the profile of user.
	 *
	 * @OA\Tag(name="user")
	 *
	 * @Rest\Patch("user/{id}/profile", requirements={"id" = "\d+"})
	 * @OA\Response(
	 * 		response="200",
	 * 		description="Success.",
	 *      @Model(type=ProfileModel::class)
	 * )
	 */
	public function patchUserProfile(int $id): Response
	{
		return $this->handleView($this->view(new ProfileModel(), 200));
	}

	/**
	 * DRAFT: Modifies my user profile.
	 *
	 * @OA\Tag(name="my")
	 *
	 * @Rest\Patch("user/current/profile")
	 * @OA\Response(
	 * 		response="200",
	 * 		description="Success.",
	 *      @Model(type=ProfileModel::class)
	 * )
	 */
	public function patchMyUserProfile(): Response
	{
		return $this->handleView($this->view(new ProfileModel(), 200));
	}
}
