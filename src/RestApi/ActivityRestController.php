<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Activity\ActivityTransactions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

use function intval;

class ActivityRestController extends AbstractFOSRestController
{
	public function __construct(
		private readonly ActivityTransactions $activityTransactions,
		private readonly Session $session
	) {
	}

	/**
	 * Returns the filters for all dashboard activities for the current user.
	 *
	 * @OA\Tag(name="activities")
	 * @Rest\Get("activities/filters")
	 * @OA\Response(response=Response::HTTP_OK, description="Success.")
	 * @OA\Response(response=Response::HTTP_UNAUTHORIZED, description="Not logged in.")
	 */
	public function getActivityFiltersAction(): Response
	{
		if (!$this->session->id()) {
			throw new UnauthorizedHttpException('');
		}

		$filters = $this->activityTransactions->getFilters();

		return $this->handleView($this->view($filters, Response::HTTP_OK));
	}

	/**
	 * Sets which dashboard activities should be deactivated for the current user.
	 *
	 * @OA\Tag(name="activities")
	 * @Rest\Patch("activities/filters")
	 * @Rest\RequestParam(name="excluded")
	 * @OA\Response(response=Response::HTTP_OK, description="Success.")
	 * @OA\Response(response=Response::HTTP_UNAUTHORIZED, description="Not logged in.")
	 */
	public function setActivityFiltersAction(ParamFetcher $paramFetcher): Response
	{
		if (!$this->session->id()) {
			throw new UnauthorizedHttpException('');
		}

		$excluded = $paramFetcher->get('excluded');
		$this->activityTransactions->setExcludedFilters($excluded);

		return $this->handleView($this->view([], Response::HTTP_OK));
	}

	/**
	 * Returns the updates object for ActivityOverview to display on the dashboard.
	 *
	 * @OA\Tag(name="activities")
	 * @Rest\Get("activities/updates")
	 * @Rest\QueryParam(name="page", requirements="\d+", default="0", description="Which page of updates to return")
	 * @OA\Response(response=Response::HTTP_OK, description="Success.")
	 * @OA\Response(response=Response::HTTP_UNAUTHORIZED, description="Not logged in.")
	 */
	public function getActivityUpdatesAction(ParamFetcher $paramFetcher): Response
	{
		if (!$this->session->id()) {
			throw new UnauthorizedHttpException('');
		}

		$page = intval($paramFetcher->get('page'));

		$updates = [
			'updates' => $this->activityTransactions->getUpdateData($page),
		];

		return $this->handleView($this->view($updates, Response::HTTP_OK));
	}
}
