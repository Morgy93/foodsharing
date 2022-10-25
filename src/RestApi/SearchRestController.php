<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Core\DBConstants\Unit\UnitType;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Search\SearchGateway;
use Foodsharing\Modules\Search\SearchTransactions;
use Foodsharing\Permissions\ForumPermissions;
use Foodsharing\Permissions\SearchPermissions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class SearchRestController extends AbstractFOSRestController
{
	private Session $session;
	private SearchGateway $searchGateway;
	private SearchTransactions $searchTransactions;
	private ForumPermissions $forumPermissions;
	private SearchPermissions $searchPermissions;

	public function __construct(
		Session $session,
		SearchGateway $searchGateway,
		SearchTransactions $searchTransactions,
		ForumPermissions $forumPermissions,
		SearchPermissions $searchPermissions
	) {
		$this->session = $session;
		$this->searchGateway = $searchGateway;
		$this->searchTransactions = $searchTransactions;
		$this->forumPermissions = $forumPermissions;
		$this->searchPermissions = $searchPermissions;
	}

	/**
	 * @OA\Tag(name="search")
	 *
	 * @Rest\Get("search/index")
	 */
	public function getSearchLegacyIndexAction(): Response
	{
		if (!$this->session->may()) {
			throw new AccessDeniedHttpException();
		}
		$data = $this->searchTransactions->generateIndex();

		$view = $this->view($data, 200);

		return $this->handleView($view);
	}

	/**
	 * @OA\Tag(name="search")
	 *
	 * @Rest\Get("search/user")
	 * @Rest\QueryParam(name="q", description="Search query.")
	 * @Rest\QueryParam(name="regionId", requirements="\d+", nullable=true, description="Restricts the search to a region")
	 */
	public function listUserResultsAction(ParamFetcher $paramFetcher, Session $session, FoodsaverGateway $foodsaverGateway, RegionGateway $regionGateway): Response
	{
		if (!$session->id()) {
			throw new UnauthorizedHttpException('', 'not logged in');
		}

		$q = $paramFetcher->get('q');
		$regionId = $paramFetcher->get('regionId');
		if (!empty($regionId) && !$this->searchPermissions->maySearchInRegion($regionId)) {
			throw new AccessDeniedHttpException('insufficient permissions to search in that region');
		}

		if (preg_match('/^[0-9]+$/', $q) && $foodsaverGateway->foodsaverExists((int)$q)) {
			$user = $foodsaverGateway->getFoodsaverName((int)$q);
			$results = [['id' => (int)$q, 'value' => $user . ' (' . (int)$q . ')']];
		} else {
			if (!empty($regionId)) {
				$regions = [$regionId];
			} elseif (in_array(RegionIDs::EUROPE_WELCOME_TEAM, $this->session->listRegionIDs(), true) ||
				$this->session->may('orga')) {
				$regions = null;
			} else {
				$regions = array_column(array_filter(
					$regionGateway->listForFoodsaver($session->id()),
					function ($v) {
						return in_array($v['type'], UnitType::getSearchableUnitTypes());
					}
				), 'id');
				$ambassador = $regionGateway->getFsAmbassadorIds($session->id());
				foreach ($ambassador as $region) {
					/* TODO: Refactor listIdsForDescendantsAndSelf to work with multiple regions. I did not do this now as it might impose too big of a risk for the release.
					2020-05-15 NerdyProjects I will care within a few weeks!
					Anyway, the performance of this should be orders of magnitude higher than the previous implementation.
					 */
					$regions = array_merge(
						$regions,
						$regionGateway->listIdsForDescendantsAndSelf($region)
					);
				}
				$regions = array_unique($regions);
			}

			$results = $this->searchGateway->searchUserInGroups(
				$q,
				false,
				$regions
			);
			$results = array_map(function ($v) { return ['id' => $v->id, 'value' => $v->name . ' (' . $v->id . ')']; }, $results);
		}

		return $this->handleView($this->view($results, 200));
	}

	/**
	 * General search endpoint that returns foodsavers, stores, and regions.
	 *
	 * @OA\Tag(name="search")
	 *
	 * @Rest\Get("search/all")
	 * @Rest\QueryParam(name="q", description="Search query.")
	 */
	public function searchAction(ParamFetcher $paramFetcher): Response
	{
		if (!$this->session->may()) {
			throw new UnauthorizedHttpException('');
		}

		$q = $paramFetcher->get('q');
		if (empty($q)) {
			throw new BadRequestHttpException();
		}

		$results = $this->searchTransactions->search($q);

		return $this->handleView($this->view($results, 200));
	}

	/**
	 * Searches in the titles of forum threads in a specific group.
	 *
	 * @OA\Parameter(name="groupId", in="path", @OA\Schema(type="integer"), description="which forum to return threads for (region or group)")
	 * @OA\Parameter(name="subforumId", in="path", @OA\Schema(type="integer"), description="ID of the forum in the group (normal or ambassador forum)")
	 * @OA\Parameter(name="q", in="query", @OA\Schema(type="string"), description="search query")
	 * @OA\Response(response="200", description="Success",
	 *     @OA\Schema(type="object", @OA\Property(property="data", type="array",
	 *         @OA\Items(type="object",
	 *             @OA\Property(property="id", type="integer", description="thread id"),
	 *             @OA\Property(property="name", type="string", description="thread title")
	 *         )
	 *     ))
	 * )
	 * @OA\Response(response="400", description="Empty search query.")
	 * @OA\Response(response="403", description="Insufficient permissions to search in that forum.")
	 * @OA\Tag(name="search")
	 *
	 * @Rest\Get("search/forum/{groupId}/{subforumId}", requirements={"groupId" = "\d+", "subforumId" = "\d+"})
	 * @Rest\QueryParam(name="q", description="Search query.", nullable=false)
	 */
	public function searchForumTitleAction(int $groupId, int $subforumId, ParamFetcher $paramFetcher): Response
	{
		if (!$this->session->id()) {
			throw new UnauthorizedHttpException('');
		}
		if (!$this->forumPermissions->mayAccessForum($groupId, $subforumId)) {
			throw new AccessDeniedHttpException();
		}

		$q = $paramFetcher->get('q');
		if (empty($q)) {
			throw new BadRequestHttpException();
		}

		$results = $this->searchGateway->searchForumTitle($q, $groupId, $subforumId);

		return $this->handleView($this->view($results, 200));
	}
}
