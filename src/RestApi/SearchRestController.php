<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Search\DTO\MixedSearchResult;
use Foodsharing\Modules\Search\DTO\SimplifiedUserSearchResult;
use Foodsharing\Modules\Search\SearchGateway;
use Foodsharing\Modules\Search\SearchTransactions;
use Foodsharing\Permissions\ForumPermissions;
use Foodsharing\Permissions\SearchPermissions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\Model;
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
        SearchPermissions $searchPermissions,
    ) {
        $this->session = $session;
        $this->searchGateway = $searchGateway;
        $this->searchTransactions = $searchTransactions;
        $this->forumPermissions = $forumPermissions;
        $this->searchPermissions = $searchPermissions;
    }

    /**
     * @OA\Tag(name="search")
     * @Rest\Get("search/user")
     * @Rest\QueryParam(name="q", description="Search query.")
     * @Rest\QueryParam(name="regionId", requirements="\d+", nullable=true, description="Restricts the search to a region")
     * @OA\RequestBody(@OA\JsonContent(
     *    type="array",
     *    @OA\Items(ref=@Model(type=SimplifiedUserSearchResult::class))
     * ))
     */
    public function listUserResultsAction(ParamFetcher $paramFetcher, Session $session): Response
    {
        if (!$session->id()) {
            throw new UnauthorizedHttpException('', 'not logged in');
        }

        $q = $paramFetcher->get('q');
        $regionId = $paramFetcher->get('regionId');
        $maySearchByEmailAddress = $this->searchPermissions->maySearchByEmailAddress();

        if (!$regionId) {
            $users = $this->searchGateway->searchUsers($q, $this->session->id(), false, $maySearchByEmailAddress);
        } elseif (!$this->searchPermissions->maySearchInRegion($regionId)) {
            throw new AccessDeniedHttpException('insufficient permissions to search in that region');
        } else {
            $users = $this->searchGateway->searchUsersGlobal($q, $regionId, false, false);
        }

        $users = array_map(fn ($user) => SimplifiedUserSearchResult::fromUserSearchResult($user), $users);

        return $this->handleView($this->view($users, 200));
    }

    /**
     * General search endpoint that returns foodsavers, stores, and regions, food share points and working groups.
     *
     * @OA\Tag(name="search")
     * @Rest\Get("search/all")
     * @Rest\QueryParam(name="q", description="Search query.")
     * @OA\Response(
     * 		response="200",
     * 		description="Success.",
     *      @Model(type=MixedSearchResult::class)
     * )
     */
    public function searchAction(ParamFetcher $paramFetcher): Response
    {
        if (!$this->session->mayRole()) {
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
     * @OA\Response(response="200", description="Success", @OA\Schema(type="array"))
     * @OA\Response(response="400", description="Empty search query.")
     * @OA\Response(response="403", description="Insufficient permissions to search in that forum.")
     * @OA\Tag(name="search")
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

        $disableRegionCheck = $this->forumPermissions->maySearchEveryForum();
        $results = $this->searchGateway->searchThreads($q, $this->session->id(), $groupId, $subforumId, $disableRegionCheck);

        return $this->handleView($this->view($results, 200));
    }
}
