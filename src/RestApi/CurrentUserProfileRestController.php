<?php

namespace Foodsharing\RestApi;

use Foodsharing\Modules\Foodsaver\DTO\EditableProfileDTO;
use Foodsharing\RestApi\Models\Profile\ProfileModel;
use Foodsharing\RestApi\Models\User\UserPermissionsModel;
use Foodsharing\RestApi\Models\User\UserStatisticsModel;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Foodsharing\Annotation\DisableCsrfProtection;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CurrentUserProfileRestController extends AbstractFOSRestController
{
	public function __construct()
	{
	}

	/**
	 * DRAFT: Provides my user profile information.
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
	 * DRAFT: Provides my user permissions in foodsharing platform.
	 *
	 * @OA\Tag(name="my")
	 *
	 * @Rest\Get("user/current/permissions")
	 *
	 * @OA\Response(
	 * 		response="200",
	 * 		description="Success.",
	 *      @Model(type=UserPermissionsModel::class)
	 * )
	 */
	public function getMyUserPermissions(EditableProfileDTO $profile): Response
	{
		return $this->handleView($this->view(new UserPermissionsModel(), 200));
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
	 * DRAFT: Provides my user profile information.
	 *
	 * @OA\Tag(name="my")
	 *
	 * @Rest\Patch("user/current/profile")
	 * 
	 * @DisableCsrfProtection
	 * 
	 * @OA\RequestBody(@Model(type=EditableProfileDTO::class))
	 * @OA\Response(
	 * 		response="200",
	 * 		description="Success.",
	 *      @Model(type=EditableProfileDTO::class)
	 * )
	 */
	public function patchProfile(Request  $request, SerializerInterface  $serializer, ValidatorInterface $validator): Response
	{
		$dto = $serializer->deserialize($request->getContent(), EditableProfileDTO::class, 'json');
        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            throw new BadRequestException((string) $errors);
        }

		return $this->handleView($this->view($dto, 200));
	}

	/**
	 * DRAFT: Allow user to request a change of essential user information.
	 *
	 * Name
	 * Firstname
	 * Gender? (Why?)
	 * E-Mail?
	 *
	 * @OA\Tag(name="my")
	 *
	 * @Rest\post("user/current/profile/changeEssentialIdentifier")
	 * @OA\Response(
	 * 		response="200",
	 * 		description="Success.",
	 *      @Model(type=ProfileModel::class)
	 * )
	 */
	public function postChangeEssentialSettings(): Response
	{
		return $this->handleView($this->view(new ProfileModel(), 200));
	}
}
