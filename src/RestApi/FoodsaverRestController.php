<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Profile\ProfileGateway;
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

final class FoodsaverRestController extends AbstractFOSRestController
{
	private FoodsaverGateway $foodsaverGateway;
	private ProfileGateway $profileGateway;
	private PickupGateway $pickupGateway;
	private ProfilePermissions $profilePermissions;
	private Session $session;

	public function __construct(
		FoodsaverGateway $foodsaverGateway,
		ProfileGateway $profileGateway,
		PickupGateway $pickupGateway,
		ProfilePermissions $profilePermissions,
		Session $session
	) {
		$this->foodsaverGateway = $foodsaverGateway;
		$this->profileGateway = $profileGateway;
		$this->pickupGateway = $pickupGateway;
		$this->profilePermissions = $profilePermissions;
		$this->session = $session;
	}

	/**
	 * Lists all pickups into which a user is signed in on a specific day, including unconfirmed ones.
	 * This only works for future pickups.
	 *
	 * @OA\Tag(name="foodsaver")
	 * @Rest\Get("foodsaver/{fsId}/pickups/{onDate}", requirements={"fsId" = "\d+", "onDate" = "[^/]+"})
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

		return $this->handleView($this->view($pickups));
	}
}
