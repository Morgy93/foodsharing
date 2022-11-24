<?php

namespace Foodsharing\RestApi;

use Exception;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Application\ApplicationTransactions;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Permissions\WorkGroupPermissions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class ApplicationRestController extends AbstractFOSRestController
{
	public function __construct(
		private readonly WorkGroupPermissions $workGroupPermissions,
		private readonly RegionGateway $regionGateway,
		private readonly ApplicationTransactions $applicationTransactions,
		private readonly Session $session
	) {
	}

	/**
	 * Accepts an application for a work group.
	 *
	 * @OA\Tag(name="application")
	 * @Rest\Patch("applications/{groupId}/{userId}", requirements={"groupId" = "\d+", "userId" = "\d+"})
	 * @OA\Parameter(name="groupId", in="path", @OA\Schema(type="integer"), description="which work group the request is for")
	 * @OA\Response(response=Response::HTTP_OK, description="Success.")
	 * @OA\Response(response=Response::HTTP_UNAUTHORIZED, description="Not logged in.")
	 * @OA\Response(response=Response::HTTP_FORBIDDEN, description="Insufficient permissions.")
	 * @OA\Response(response=Response::HTTP_NOT_FOUND, description="Workgroup does not exist.")
	 */
	public function acceptApplicationAction(int $groupId, int $userId): Response
	{
		if (!$this->session->id()) {
			throw new UnauthorizedHttpException('');
		}

		try {
			$group = $this->regionGateway->getRegion($groupId);
		} catch (Exception $e) {
			throw new NotFoundHttpException();
		}

		if (!$this->workGroupPermissions->mayEdit($group)) {
			throw new AccessDeniedHttpException();
		}

		$this->applicationTransactions->acceptApplication($group, $userId);

		return $this->handleView($this->view([], Response::HTTP_FORBIDDEN));
	}

	/**
	 * Declines an application for a work group.
	 *
	 * @OA\Tag(name="application")
	 * @Rest\Delete("applications/{groupId}/{userId}", requirements={"groupId" = "\d+", "userId" = "\d+"})
	 * @OA\Parameter(name="groupId", in="path", @OA\Schema(type="integer"), description="which work group the request is for")
	 * @OA\Response(response=Response::HTTP_OK, description="Success.")
	 * @OA\Response(response=Response::HTTP_UNAUTHORIZED, description="Not logged in.")
	 * @OA\Response(response=Response::HTTP_FORBIDDEN, description="Insufficient permissions.")
	 * @OA\Response(response=Response::HTTP_NOT_FOUND, description="Workgroup does not exist.")
	 */
	public function declineApplicationAction(int $groupId, int $userId): Response
	{
		if (!$this->session->id()) {
			throw new UnauthorizedHttpException('');
		}

		try {
			$group = $this->regionGateway->getRegion($groupId);
		} catch (Exception $e) {
			throw new NotFoundHttpException();
		}

		if (!$this->workGroupPermissions->mayEdit($group)) {
			throw new AccessDeniedHttpException();
		}

		$this->applicationTransactions->declineApplication($group, $userId);

		return $this->handleView($this->view([], Response::HTTP_OK));
	}
}
