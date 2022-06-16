<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Core\DBConstants\Bell\BellType;
use Foodsharing\Modules\Core\DBConstants\Store\Milestone;
use Foodsharing\Modules\Core\DBConstants\Store\StoreLogAction;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Store\DTO\Store;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Modules\Store\StoreTransactions;
use Foodsharing\Modules\Store\TeamStatus as TeamMembershipStatus;
use Foodsharing\Permissions\StorePermissions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class StoreRestController extends AbstractFOSRestController
{
	private Session $session;
	private FoodsaverGateway $foodsaverGateway;
	private StoreGateway $storeGateway;
	private StoreTransactions $storeTransactions;
	private StorePermissions $storePermissions;
	private BellGateway $bellGateway;

	// literal constants
	private const NOT_LOGGED_IN = 'not logged in';
	private const ID = 'id';

	public function __construct(
		Session $session,
		FoodsaverGateway $foodsaverGateway,
		StoreGateway $storeGateway,
		StoreTransactions $storeTransactions,
		StorePermissions $storePermissions,
		BellGateway $bellGateway
	) {
		$this->session = $session;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->storeGateway = $storeGateway;
		$this->storeTransactions = $storeTransactions;
		$this->storePermissions = $storePermissions;
		$this->bellGateway = $bellGateway;
	}

	/**
	 * Returns details of the store with the given ID. Returns 200 and the
	 * store, 404 if the store does not exist, or 401 if not logged in.
	 *
	 * @OA\Tag(name="stores")
	 *
	 * @Rest\Get("stores/{storeId}", requirements={"storeId" = "\d+"})
	 */
	public function getStoreAction(int $storeId): Response
	{
		if (!$this->session->may('fs')) {
			throw new UnauthorizedHttpException(self::NOT_LOGGED_IN);
		}
		$maySeeDetails = $this->storePermissions->mayAccessStore($storeId);

		$store = $this->storeGateway->getBetrieb($storeId, $maySeeDetails);

		if (!$store || !isset($store[self::ID])) {
			throw new NotFoundHttpException('Store does not exist.');
		}

		$store = RestNormalization::normalizeStore($store, $maySeeDetails);

		return $this->handleView($this->view(['store' => $store], 200));
	}

	/**
	 * @OA\Tag(name="stores")
	 *
	 * @Rest\Get("user/current/stores")
	 */
	public function getFilteredStoresForUserAction(): Response
	{
		if (!$this->session->may()) {
			throw new UnauthorizedHttpException(self::NOT_LOGGED_IN);
		}

		$filteredStoresForUser = $this->storeTransactions->getFilteredStoresForUser($this->session->id());

		if ($filteredStoresForUser === []) {
			return $this->handleView($this->view([], 204));
		}

		return $this->handleView($this->view($filteredStoresForUser, 200));
	}

	/**
	 * Get "wallposts" for store with given ID. Returns 200 and the comments,
	 * 401 if not logged in, or 403 if you may not view this store.
	 *
	 * @OA\Tag(name="stores")
	 *
	 * @Rest\Get("stores/{storeId}/posts", requirements={"storeId" = "\d+"})
	 */
	public function getStorePosts(int $storeId): Response
	{
		if (!$this->session->may()) {
			throw new UnauthorizedHttpException(self::NOT_LOGGED_IN);
		}
		if (!$this->storePermissions->mayReadStoreWall($storeId)) {
			throw new AccessDeniedHttpException();
		}

		$notes = $this->storeGateway->getStorePosts($storeId) ?? [];
		$notes = array_map(function ($n) {
			return RestNormalization::normalizeStoreNote($n);
		}, $notes);

		return $this->handleView($this->view($notes, 200));
	}

	/**
	 * Write a new "wallpost" for the given store. Returns 200 and the created entry,
	 * 401 if not logged in, or 403 if you may not view this store.
	 *
	 * @OA\Tag(name="stores")
	 *
	 * @Rest\Post("stores/{storeId}/posts")
	 * @Rest\RequestParam(name="text")
	 */
	public function addStorePostAction(int $storeId, ParamFetcher $paramFetcher): Response
	{
		if (!$this->session->may()) {
			throw new UnauthorizedHttpException(self::NOT_LOGGED_IN);
		}
		if (!$this->storePermissions->mayWriteStoreWall($storeId)) {
			throw new AccessDeniedHttpException();
		}

		$author = $this->session->id();
		$text = $paramFetcher->get('text');
		$note = [
			'foodsaver_id' => $author,
			'betrieb_id' => $storeId,
			'text' => $text,
			'zeit' => date('Y-m-d H:i:s'),
			'milestone' => Milestone::NONE,
			'last' => 1
		];
		$postId = $this->storeGateway->addStoreWallpost($note);

		$storeName = $this->storeGateway->getStoreName($storeId);
		$userName = $this->session->user('name');
		$userPhoto = $this->session->user('photo');
		$team = $this->storeGateway->getStoreTeam($storeId);

		$teamWithoutPostAuthor = array_filter($team, function ($x) use ($author) {
			return $x['id'] !== $author;
		});

		$bellData = Bell::create(
			'store_wallpost_title',
			'store_wallpost',
			'fas fa-thumbtack',
			['href' => '/?page=fsbetrieb&id=' . $storeId],
			[
				'user' => $userName,
				'name' => $storeName
			],
			BellType::createIdentifier(BellType::STORE_WALL_POST, $storeId)
		);

		$this->bellGateway->addBell($teamWithoutPostAuthor, $bellData);

		$note = $this->storeGateway->getStoreWallpost($storeId, $postId);
		$note['name'] = $userName;
		$note['photo'] = $userPhoto;
		$post = RestNormalization::normalizeStoreNote($note);

		return $this->handleView($this->view(['post' => $post], 200));
	}

	/**
	 * Deletes a post from the wall of a store. Returns 200 upon successful deletion,
	 * 401 if not logged in, or 403 if you may not remove this particular "wallpost".
	 *
	 * @OA\Tag(name="stores")
	 *
	 * @Rest\Delete("stores/{storeId}/posts/{postId}")
	 */
	public function deleteStorePostAction(int $storeId, int $postId): Response
	{
		if (!$this->session->may()) {
			throw new UnauthorizedHttpException(self::NOT_LOGGED_IN);
		}
		if (!$this->storePermissions->mayDeleteStoreWallPost($storeId, $postId)) {
			throw new AccessDeniedHttpException();
		}
		$result = $this->storeGateway->getStoreWallpost($storeId, $postId);

		$this->storeGateway->addStoreLog($result['betrieb_id'], $this->session->id(), $result['foodsaver_id'], new \DateTime($result['zeit']), StoreLogAction::DELETED_FROM_WALL, $result['text']);

		$this->storeGateway->deleteStoreWallpost($storeId, $postId);

		return $this->handleView($this->view([], 200));
	}

	/**
	 * Request to join a store team.
	 *
	 * @OA\Parameter(name="storeId", in="path", @OA\Schema(type="integer"), description="for which store to apply")
	 * @OA\Parameter(name="userId", in="path", @OA\Schema(type="integer"), description="user that wants to be accepted")
	 * @OA\Response(response="200", description="Success")
	 * @OA\Response(response="401", description="Not logged in")
	 * @OA\Response(response="403", description="Insufficient permissions to be member of a store team")
	 * @OA\Response(response="404", description="Store does not exist")
	 * @OA\Response(response="422", description="Already applied or already member of this store team")
	 * @OA\Tag(name="stores")
	 *
	 * @Rest\Post("stores/{storeId}/requests/{userId}")
	 */
	public function requestStoreTeamMembershipAction(int $storeId, int $userId): Response
	{
		if (!$this->session->id()) {
			throw new UnauthorizedHttpException(self::NOT_LOGGED_IN);
		}
		if (!$this->storeGateway->storeExists($storeId)) {
			throw new NotFoundHttpException('Store does not exist.');
		}
		if (!$this->storePermissions->mayJoinStoreRequest($storeId, $userId)) {
			throw new AccessDeniedHttpException();
		}
		if ($this->storeGateway->getUserTeamStatus($userId, $storeId) !== TeamMembershipStatus::NoMember) {
			throw new UnprocessableEntityHttpException('User has already applied or is already member of this store.');
		}

		$this->storeTransactions->requestStoreTeamMembership($storeId, $userId);

		return $this->handleView($this->view([], 200));
	}

	/**
	 * Accepts a user's request for joining a store.
	 *
	 * @OA\Parameter(name="storeId", in="path", @OA\Schema(type="integer"), description="for which store to accept a request")
	 * @OA\Parameter(name="userId", in="path", @OA\Schema(type="integer"), description="who should be accepted")
	 * @OA\Response(response="200", description="Success")
	 * @OA\Response(response="401", description="Not logged in")
	 * @OA\Response(response="403", description="Insufficient permissions to accept requests")
	 * @OA\Response(response="404", description="Store or request does not exist")
	 * @OA\Tag(name="stores")
	 *
	 * @Rest\Patch("stores/{storeId}/requests/{userId}")
	 * @Rest\RequestParam(name="moveToStandby", nullable=true, description="whether the new member should become part of the standby team instead of the regular team")
	 */
	public function acceptStoreRequestAction(int $storeId, int $userId, ParamFetcher $paramFetcher): Response
	{
		$this->handleEditTeamExceptions($storeId, $userId, true);
		if ($this->storeGateway->getUserTeamStatus($userId, $storeId) !== TeamMembershipStatus::Applied) {
			throw new NotFoundHttpException('Request does not exist.');
		}

		$moveToStandby = boolval($paramFetcher->get('moveToStandby'));
		$this->storeTransactions->acceptStoreRequest($storeId, $userId, $moveToStandby);

		return $this->handleView($this->view([], 200));
	}

	/**
	 * Removes the user's own request or denies another user's request for a store.
	 *
	 * @OA\Parameter(name="storeId", in="path", @OA\Schema(type="integer"), description="for which store to remove a request")
	 * @OA\Parameter(name="userId", in="path", @OA\Schema(type="integer"), description="whose request should be removed")
	 * @OA\Response(response="200", description="Success")
	 * @OA\Response(response="401", description="Not logged in")
	 * @OA\Response(response="403", description="Insufficient permissions to remove the request")
	 * @OA\Response(response="404", description="Store or request does not exist")
	 * @OA\Tag(name="stores")
	 *
	 * @Rest\Delete("stores/{storeId}/requests/{userId}")
	 */
	public function declineStoreRequestAction(int $storeId, int $userId): Response
	{
		$this->handleEditTeamExceptions($storeId, $userId, false, true);
		if ($this->storeGateway->getUserTeamStatus($userId, $storeId) !== TeamMembershipStatus::Applied) {
			throw new NotFoundHttpException('Request does not exist.');
		}

		$this->storeTransactions->declineStoreRequest($storeId, $userId);

		if ($this->session->id() == $userId) {
			$LogAction = StoreLogAction::REQUEST_CANCELLED;
		} else {
			$LogAction = StoreLogAction::REQUEST_DECLINED;
		}

		$this->storeGateway->addStoreLog($storeId, $this->session->id(), $userId, null, $LogAction);

		return $this->handleView($this->view([], 200));
	}

	/**
	 * Adds user to store team, without a request to join from that user.
	 *
	 * @OA\Parameter(name="storeId", in="path", @OA\Schema(type="integer"), description="which store to manage")
	 * @OA\Parameter(name="userId", in="path", @OA\Schema(type="integer"), description="which user to add to the store team")
	 * @OA\Response(response="200", description="Success")
	 * @OA\Response(response="401", description="Not logged in")
	 * @OA\Response(response="403", description="Insufficient permissions to manage this store team")
	 * @OA\Response(response="404", description="Store does not exist")
	 * @OA\Response(response="422", description="User is already, or cannot be, part of this store team")
	 * @OA\Tag(name="stores")
	 *
	 * @Rest\Post("stores/{storeId}/members/{userId}")
	 */
	public function addStoreMemberAction(int $storeId, int $userId): Response
	{
		$this->handleEditTeamExceptions($storeId, $userId, true);
		$userRole = $this->foodsaverGateway->getRole($userId);
		if (!$this->storePermissions->mayAddUserToStoreTeam($storeId, $userId, $userRole)) {
			throw new UnprocessableEntityHttpException();
		}

		$this->storeTransactions->addStoreMember($storeId, $userId);

		return $this->handleView($this->view([], 200));
	}

	/**
	 * Removes user from store team.
	 *
	 * @OA\Parameter(name="storeId", in="path", @OA\Schema(type="integer"), description="which store to manage")
	 * @OA\Parameter(name="userId", in="path", @OA\Schema(type="integer"), description="which user to remove from the store team")
	 * @OA\Response(response="200", description="Success")
	 * @OA\Response(response="401", description="Not logged in")
	 * @OA\Response(response="403", description="Insufficient permissions to manage this store team (if user is not yourself)")
	 * @OA\Response(response="404", description="Store does not exists or user is not a member of it")
	 * @OA\Response(response="422", description="User cannot currently leave this team")
	 * @OA\Tag(name="stores")
	 *
	 * @Rest\Delete("stores/{storeId}/members/{userId}")
	 */
	public function removeStoreMemberAction(int $storeId, int $userId): Response
	{
		$this->handleEditTeamExceptions($storeId, $userId, false, true);
		if (!$this->storePermissions->mayLeaveStoreTeam($storeId, $userId)) {
			throw new UnprocessableEntityHttpException();
		}

		$this->storeTransactions->removeStoreMember($storeId, $userId);

		return $this->handleView($this->view([], 200));
	}

	/**
	 * Promotes a user to store manager.
	 *
	 * @OA\Parameter(name="storeId", in="path", @OA\Schema(type="integer"), description="which store to manage")
	 * @OA\Parameter(name="userId", in="path", @OA\Schema(type="integer"), description="which user to add as manager")
	 * @OA\Response(response="200", description="Success")
	 * @OA\Response(response="401", description="Not logged in")
	 * @OA\Response(response="403", description="Insufficient permissions to manage this store team")
	 * @OA\Response(response="404", description="Store does not exist")
	 * @OA\Response(response="422", description="User cannot become manager of this store")
	 * @OA\Tag(name="stores")
	 *
	 * @Rest\Patch("stores/{storeId}/managers/{userId}")
	 */
	public function addStoreManagerAction(int $storeId, int $userId): Response
	{
		$this->handleEditTeamExceptions($storeId, $userId, true);
		$userRole = $this->foodsaverGateway->getRole($userId);
		if (!$this->storePermissions->mayBecomeStoreManager($storeId, $userId, $userRole)) {
			throw new UnprocessableEntityHttpException();
		}

		$this->storeTransactions->makeMemberResponsible($storeId, $userId);

		return $this->handleView($this->view([], 200));
	}

	/**
	 * Demotes a user from store manager to regular store team member.
	 *
	 * @OA\Parameter(name="storeId", in="path", @OA\Schema(type="integer"), description="which store to manage")
	 * @OA\Parameter(name="userId", in="path", @OA\Schema(type="integer"), description="which user to remove as manager")
	 * @OA\Response(response="200", description="Success")
	 * @OA\Response(response="401", description="Not logged in")
	 * @OA\Response(response="403", description="Insufficient permissions to manage this store team")
	 * @OA\Response(response="404", description="Store does not exists or user is not a member of it")
	 * @OA\Response(response="422", description="User cannot lose responsibility for this store")
	 * @OA\Tag(name="stores")
	 *
	 * @Rest\Delete("stores/{storeId}/managers/{userId}")
	 */
	public function removeStoreManagerAction(int $storeId, int $userId): Response
	{
		$this->handleEditTeamExceptions($storeId, $userId);
		if (!$this->storePermissions->mayLoseStoreManagement($storeId, $userId)) {
			throw new UnprocessableEntityHttpException();
		}

		$this->storeTransactions->downgradeResponsibleMember($storeId, $userId);

		return $this->handleView($this->view([], 200));
	}

	/**
	 * Moves a store-team member from the regular team to the standby team.
	 * Will also succeed if the member was already part of the standby team.
	 *
	 * @OA\Parameter(name="storeId", in="path", @OA\Schema(type="integer"), description="team of which store to manage")
	 * @OA\Parameter(name="userId", in="path", @OA\Schema(type="integer"), description="who should be moved to the standby team")
	 * @OA\Response(response="200", description="Success")
	 * @OA\Response(response="401", description="Not logged in")
	 * @OA\Response(response="403", description="Insufficient permissions to manage this store team")
	 * @OA\Response(response="404", description="User is not a member of this store")
	 * @OA\Tag(name="stores")
	 *
	 * @Rest\Patch("stores/{storeId}/members/{userId}/standby")
	 */
	public function moveMemberToStandbyTeamAction(int $storeId, int $userId): Response
	{
		if (!$this->session->id()) {
			throw new UnauthorizedHttpException(self::NOT_LOGGED_IN);
		}
		if (!$this->storePermissions->mayEditStoreTeam($storeId)) {
			throw new AccessDeniedHttpException();
		}
		if ($this->storeGateway->getUserTeamStatus($userId, $storeId) === TeamMembershipStatus::NoMember) {
			throw new NotFoundHttpException('User is not a member of this store.');
		}

		$this->storeTransactions->moveMemberToStandbyTeam($storeId, $userId);

		return $this->handleView($this->view([], 200));
	}

	/**
	 * Moves a store-team member from the standby team to the regular team.
	 * Will also succeed if the member was already part of the regular team.
	 *
	 * @OA\Parameter(name="storeId", in="path", @OA\Schema(type="integer"), description="team of which store to manage")
	 * @OA\Parameter(name="userId", in="path", @OA\Schema(type="integer"), description="who should be moved to the regular store team")
	 * @OA\Response(response="200", description="Success")
	 * @OA\Response(response="401", description="Not logged in")
	 * @OA\Response(response="403", description="Insufficient permissions to manage this store team")
	 * @OA\Response(response="404", description="User is not a member of this store")
	 * @OA\Tag(name="stores")
	 *
	 * @Rest\Delete("stores/{storeId}/members/{userId}/standby")
	 */
	public function moveUserToRegularTeamAction(int $storeId, int $userId): Response
	{
		if (!$this->session->id()) {
			throw new UnauthorizedHttpException(self::NOT_LOGGED_IN);
		}
		if (!$this->storePermissions->mayEditStoreTeam($storeId)) {
			throw new AccessDeniedHttpException();
		}

		if ($this->storeGateway->getUserTeamStatus($userId, $storeId) === TeamMembershipStatus::NoMember) {
			throw new NotFoundHttpException('User is not a member of this store.');
		}

		$this->storeTransactions->moveMemberToRegularTeam($storeId, $userId);

		return $this->handleView($this->view([], 200));
	}

	/**
	 * Makes sure an edit to the team can be performed and throws an exception otherwise.
	 * The asserted properties are:
	 *  - Session is logged in
	 *  - Store exists
	 *  - Session may edit the store team (edits to the requesting user are additionally allowed with flag 'mayEditOneself')
	 *  - Given target user is the stores team (check disabled with flag 'allowExternals').
	 *
	 * @param int $storeId The id of the store
	 * @param int $targetId The id of the affected user
	 * @param bool $allowExternals Whether to allow the targeted user to be not in the team
	 * @param bool $mayEditOneself Whether to allow the action if the executing user is the target user
	 *
	 * @return void
	 */
	private function handleEditTeamExceptions(int $storeId, int $targetId, bool $allowExternals = false, bool $mayEditOneself = false)
	{
		$sessionId = $this->session->id();
		if (!$sessionId) {
			throw new UnauthorizedHttpException(self::NOT_LOGGED_IN);
		}
		if (!$this->storeGateway->storeExists($storeId)) {
			throw new NotFoundHttpException('Store does not exist.');
		}

		// Session may edit target user (mayEditStoreTeam OR (session is targetUser AND 'mayEditOneself' flag is set))
		if (!($mayEditOneself && $sessionId == $targetId) && !$this->storePermissions->mayEditStoreTeam($storeId)) {
			throw new AccessDeniedHttpException();
		}

		// Target user is in Team (or externals are allowed)
		if (!$allowExternals && $this->storeGateway->getUserTeamStatus($targetId, $storeId) === TeamMembershipStatus::NoMember) {
			throw new NotFoundHttpException('User is not a member of this store.');
		}
	}

	/**
	 * @Rest\Patch("stores/{storeId}/data/{field}", requirements={"storeId" = "\d+", "field" = "\w+"})
	 *
	 * @Rest\RequestParam(name="newValue", nullable=true)
	 *
	 * @param int $storeId ID of an existing store
	 * @param string $field which store property to update
	 */
	public function updateStoreAction(int $storeId, string $field, ParamFetcher $paramFetcher)
	{
		if (!$this->storePermissions->mayEditStore($storeId)) {
			throw new AccessDeniedHttpException();
		}

		if (empty($field)) {
			throw new UnprocessableEntityHttpException('Store field to edit cannot be empty.');
		}

		$newValue = $paramFetcher->get('newValue');

		if (!Store::isValidStoreField($field)) {
			throw new UnprocessableEntityHttpException('Cannot edit this store field: ' . $field);
		}

		if ($newValue === null && !Store::isNullable($field)) {
			throw new UnprocessableEntityHttpException('Cannot set this store field to null: ' . $field);
		} elseif (!$newValue && !Store::isEmptyable($field)) {
			throw new UnprocessableEntityHttpException('Cannot set this store field to empty value: ' . $field);
		}

		switch ($field) {
			case 'team_status':
				if (TeamStatus::isValidTeamStatus($newValue)) {
					$this->storeGateway->setStoreTeamStatus($storeId, $newValue);
				} else {
					throw new UnprocessableEntityHttpException('Team status value not supported');
				}
				break;
			case 'lebensmittel':
				if (!is_array($newValue)) {
					throw new UnprocessableEntityHttpException('FoodTypes value must be an array');
				}
				$this->storeGateway->setStoreFoodTypes($storeId, $newValue);
				break;
			default:
				$fsId = $this->session->id();
				$this->storeGateway->editStore($storeId, $field, $newValue, $fsId);
				break;
		}

		return $this->getStoreAction($storeId);
	}
}
