<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Store\PickupGateway;
use Foodsharing\Permissions\ProfilePermissions;
use Foodsharing\Utility\TimeHelper;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

use function is_null;

final class FoodsaverRestController extends AbstractFOSRestController
{
	public function __construct(
		private readonly PickupGateway $pickupGateway,
		private readonly ProfilePermissions $profilePermissions,
		private readonly Session $session
	) {
	}

	/**
	 * Lists all pickups into which a user is signed in on a specific day, including unconfirmed ones.
	 * This only works for future pickups.
	 *
	 * @OA\Tag(name="foodsaver")
	 * @Rest\Get("foodsaver/{fsId}/pickups/{onDate}", requirements={"fsId" = "\d+", "onDate" = "[^/]+"})
	 * @OA\Response(response=Response::HTTP_OK, description="Success.")
	 * @OA\Response(response=Response::HTTP_UNAUTHORIZED, description="Not logged in.")
	 * @OA\Response(response=Response::HTTP_BAD_REQUEST, description="Incorrect data.")
	 */
	public function listSameDayPickupsAction(int $fsId, string $onDate): Response
	{
		if (!$this->session->id()) {
			throw new UnauthorizedHttpException('');
		}
		if (!$this->profilePermissions->maySeePickups($fsId)) {
			throw new AccessDeniedHttpException();
		}

		// convert date string into datetime object
		$day = TimeHelper::parsePickupDate($onDate);
		if (is_null($day)) {
			throw new BadRequestHttpException('Invalid date format');
		}
		$pickups = $this->pickupGateway->getSameDayPickupsForUser($fsId, $day);

		return $this->handleView($this->view($pickups, Response::HTTP_OK));
	}
}
