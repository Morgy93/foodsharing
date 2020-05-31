<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Core\DBConstants\Store\TeamStatus;
use Foodsharing\Modules\Core\DBConstants\WallPost\StoreWallEntryType;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Modules\Store\StoreTransactions;
use Foodsharing\Permissions\StorePermissions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class StoreRestController extends AbstractFOSRestController
{
	private Session $session;
	private StoreGateway $storeGateway;
	private StoreTransactions $storeTransactions;
	private StorePermissions $storePermissions;
	private BellGateway $bellGateway;

	// literal constants
	private const NOT_LOGGED_IN = 'not logged in';
	private const ID = 'id';

	public function __construct(
		Session $session,
		StoreGateway $storeGateway,
		StoreTransactions $storeTransactions,
		StorePermissions $storePermissions,
		BellGateway $bellGateway
	) {
		$this->session = $session;
		$this->storeGateway = $storeGateway;
		$this->storeTransactions = $storeTransactions;
		$this->storePermissions = $storePermissions;
		$this->bellGateway = $bellGateway;
	}

	/**
	 * Returns details of the store with the given ID. Returns 200 and the
	 * store, 404 if the store does not exist, or 401 if not logged in.
	 *
	 * @Rest\Get("stores/{storeId}", requirements={"storeId" = "\d+"})
	 */
	public function getStoreAction(int $storeId): Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401, self::NOT_LOGGED_IN);
		}

		$store = $this->storeGateway->getBetrieb($storeId);

		if (!$store || !isset($store[self::ID])) {
			throw new HttpException(404, 'Store does not exist.');
		}

		$store = RestNormalization::normalizeStore($store);

		return $this->handleView($this->view(['store' => $store], 200));
	}

	/**
	 * @Rest\Get("user/current/stores")
	 */
	public function getFilteredStoresForUserAction(): Response
	{
		if (!$this->session->may()) {
			throw new HttpException(403, self::NOT_LOGGED_IN);
		}

		$filteredStoresForUser = $this->storeTransactions->getFilteredStoresForUser($this->session->id());

		if ($filteredStoresForUser === []) {
			return $this->handleView($this->view([], 204));
		}

		return $this->handleView($this->view($filteredStoresForUser, 200));
	}

	/**
	 * @Rest\Patch("stores/{storeId}/data/{field}", requirements={"storeId" = "\d+", "field" = "\w+"})
	 *
	 * @Rest\RequestParam(name="newValue", nullable=false)
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
			throw new HttpException(400, 'Store field to edit cannot be empty.');
		}
		// TODO check if this property exists in the schema here? (don't just write new DB stuff)
		$newValue = $paramFetcher->get('newValue');
		// TODO map to correct data type?!

		switch ($field) {
			case 'team_status':
				if (TeamStatus::isValidTeamStatus($newValue)) {
					$this->storeGateway->setStoreTeamStatus($storeId, $newValue);
				} else {
					throw new HttpException(400, 'Team status value not supported');
				}
				break;
			case 'lebensmittel':
				if (!is_array($newValue)) {
					throw new HttpException(400, 'FoodTypes value must be an array');
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

	/**
	 * Returns details of the storechain with the given ID. Returns 200 and the
	 * details as array. Throws 404 if chain not found, 401 if not logged in.
	 *
	 * @Rest\Get("chains/{chainId}", requirements={"chainId" = "\d+"})
	 */
	public function getStoreChainAction(int $chainId): Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401, self::NOT_LOGGED_IN);
		}
		$chain = $this->storeGateway->getStoreChain($chainId);

		if (!$chain) {
			throw new HttpException(404, 'Store chain does not exist.');
		}

		return $this->handleView($this->view(['chain' => $chain], 200));
	}

	/**
	 * Adds a new store-chain. Returns the created chain data.
	 * Throws 401 if not logged in, 403 if not allowed to create new chains,
	 * or 400 if chain creation failed.
	 *
	 * @Rest\Post("chains")
	 * @Rest\RequestParam(name="name", nullable=false)
	 * @Rest\RequestParam(name="logo", nullable=true)
	 */
	public function addStoreChainAction(ParamFetcher $paramFetcher): Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401, self::NOT_LOGGED_IN);
		}

		if (!$this->storePermissions->mayManageStoreChains()) {
			throw new AccessDeniedHttpException();
		}

		$name = trim($paramFetcher->get('name'));
		if (empty($name)) {
			throw new HttpException(400, 'Store chain name must not be empty.');
		}

		$logo = $paramFetcher->get('logo');

		$chainId = $this->storeGateway->addStoreChain($name, $logo);

		if (!$chainId) {
			throw new HttpException(400, 'Unable to create store chain.');
		}

		return $this->getStoreChainAction($chainId);
	}

	/**
	 * @Rest\Post("stores/{storeId}/posts")
	 * @Rest\RequestParam(name="text")
	 */
	public function addStorePostAction(int $storeId, ParamFetcher $paramFetcher)
	{
		if (!$this->storePermissions->mayWriteStoreWall($storeId)) {
			throw new AccessDeniedHttpException();
		}

		$text = $paramFetcher->get('text');
		$this->storeGateway->add_betrieb_notiz([
			'foodsaver_id' => $this->session->id(),
			'betrieb_id' => $storeId,
			'text' => $text,
			'zeit' => date('Y-m-d H:i:s'),
			'milestone' => StoreWallEntryType::TEXT_POSTED,
			'last' => 1
		]);

		$storeName = $this->storeGateway->getBetrieb($storeId)['name'];
		$team = $this->storeGateway->getStoreTeam($storeId);

		$bellData = Bell::create(
			'store_wallpost_title',
			'store_wallpost',
			'fas fa-thumbtack',
			['href' => '/?page=fsbetrieb&id=' . $storeId],
			[
				'user' => $this->session->user('name'),
				'name' => $storeName
			],
			'store-wallpost-' . $storeId
		);

		$this->bellGateway->addBell($team, $bellData);

		return $this->handleView($this->view([], 200));
	}

	/**
	 * Deletes a post from the wall of a store.
	 *
	 * @Rest\Delete("stores/posts/{postId}")
	 */
	public function deleteStorePostAction(int $postId)
	{
		if (!$this->storePermissions->mayDeleteStoreWallPost($postId)) {
			throw new AccessDeniedHttpException();
		}

		$this->storeGateway->deleteBPost($postId);

		return $this->handleView($this->view([], 200));
	}
}
