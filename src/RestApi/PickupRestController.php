<?php

namespace Foodsharing\RestApi;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Store\StoreLogAction;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Message\MessageTransactions;
use Foodsharing\Modules\Store\PickupGateway;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Modules\Store\StoreTransactions;
use Foodsharing\Permissions\ProfilePermissions;
use Foodsharing\Permissions\StorePermissions;
use Foodsharing\Utility\TimeHelper;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcher;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

final class PickupRestController extends AbstractFOSRestController
{
	private FoodsaverGateway $foodsaverGateway;
	private Session $session;
	private PickupGateway $pickupGateway;
	private StoreGateway $storeGateway;
	private StorePermissions $storePermissions;
	private ProfilePermissions $profilePermissions;
	private StoreTransactions $storeTransactions;
	private MessageTransactions $messageTransactions;

	public function __construct(
		FoodsaverGateway $foodsaverGateway,
		Session $session,
		PickupGateway $pickupGateway,
		StoreGateway $storeGateway,
		StorePermissions $storePermissions,
		ProfilePermissions $profilePermissions,
		StoreTransactions $storeTransactions,
		MessageTransactions $messageTransactions
	) {
		$this->foodsaverGateway = $foodsaverGateway;
		$this->session = $session;
		$this->pickupGateway = $pickupGateway;
		$this->storeGateway = $storeGateway;
		$this->storePermissions = $storePermissions;
		$this->profilePermissions = $profilePermissions;
		$this->storeTransactions = $storeTransactions;
		$this->messageTransactions = $messageTransactions;
	}

	/**
	 * @OA\Tag(name="pickup")
	 *
	 * @Rest\Post("stores/{storeId}/pickups/{pickupDate}/{fsId}", requirements={"storeId" = "\d+", "pickupDate" = "[^/]+", "fsId" = "\d+"})
	 */
	public function joinPickupAction(int $storeId, string $pickupDate, int $fsId): Response
	{
		if (!$this->session->id()) {
			throw new UnauthorizedHttpException('');
		}
		if ($fsId != $this->session->id()) {
			/* currently it is forbidden to add other users to a pickup */
			throw new AccessDeniedHttpException();
		}
		if (!$this->storePermissions->mayDoPickup($storeId)) {
			throw new AccessDeniedHttpException();
		}

		$date = TimeHelper::parsePickupDate($pickupDate);
		if (is_null($date)) {
			throw new BadRequestHttpException('Invalid date format');
		}

		$isConfirmed = $this->storeTransactions->joinPickup($storeId, $date, $fsId, $this->session->id());

		$this->storeGateway->addStoreLog($storeId, $fsId, null, $date, StoreLogAction::SIGN_UP_SLOT);

		return $this->handleView($this->view([
			'isConfirmed' => $isConfirmed
		], 200));
	}

	/**
	 * Remove a user from a pickup.
	 *
	 * @OA\Tag(name="pickup")
	 *
	 * @Rest\Delete("stores/{storeId}/pickups/{pickupDate}/{fsId}", requirements={"storeId" = "\d+", "pickupDate" = "[^/]+", "fsId" = "\d+"})
	 * @RequestParam(name="message", nullable=true, default="")
	 * @RequestParam(name="sendKickMessage", nullable=true, default=true)
	 */
	public function leavePickupAction(int $storeId, string $pickupDate, int $fsId, ParamFetcher $paramFetcher): Response
	{
		if (!$this->session->id()) {
			throw new UnauthorizedHttpException('');
		}
		if (!$this->storePermissions->mayRemovePickupUser($storeId, $fsId)) {
			throw new AccessDeniedHttpException();
		}

		$message = trim($paramFetcher->get('message'));
		$sendKickMessage = $paramFetcher->get('sendKickMessage') || !$this->profilePermissions->mayCancelSlotsFromProfile($fsId);
		$this->leavePickup($storeId, $pickupDate, $fsId, $message, $sendKickMessage);

		return $this->handleView($this->view([], 200));
	}

	/**
	 * Remove a user from all his pickups.
	 *
	 * @OA\Tag(name="pickup")
	 *
	 * @Rest\Delete("pickups/{fsId}", requirements={"fsId" = "\d+"})
	 * @RequestParam(name="message", nullable=true, default="")
	 * @RequestParam(name="sendKickMessage", nullable=true, default=true)
	 */
	public function leaveAllPickupsAction(int $fsId, ParamFetcher $paramFetcher)
	{
		if (!$this->session->id()) {
			throw new UnauthorizedHttpException('');
		}
		if (!$this->profilePermissions->mayCancelSlotsFromProfile($fsId)) {
			throw new AccessDeniedHttpException();
		}
		$pickups = $this->pickupGateway->getNextPickups($fsId);
		$message = trim($paramFetcher->get('message'));
		$sendKickMessage = $paramFetcher->get('sendKickMessage');

		foreach ($pickups as $pickup) {
			$this->leavePickup($pickup['store_id'], date(DATE_ATOM, $pickup['timestamp']), $fsId, $message, $sendKickMessage);
		}

		return $this->handleView($this->view([], 200));
	}

	private function leavePickup(int $storeId, string $pickupDate, int $fsId, string $message = '', bool $sendKickMessage = true)
	{
		$date = TimeHelper::parsePickupDate($pickupDate);
		if (is_null($date)) {
			throw new BadRequestHttpException('Invalid date format');
		}

		if ($date < Carbon::now()) {
			throw new BadRequestHttpException('Cannot modify pickup in the past.');
		}

		if (!$this->pickupGateway->removeFetcher($fsId, $storeId, $date)) {
			throw new BadRequestHttpException('Failed to remove user from pickup');
		}

		if ($this->session->id() === $fsId) {
			$this->storeGateway->addStoreLog( // the user removed their own pickup
				$storeId,
				$fsId,
				null,
				$date,
				StoreLogAction::SIGN_OUT_SLOT
			);
		} else {
			$this->storeGateway->addStoreLog( // the user got kicked/the pickup got denied
				$storeId,
				$this->session->id(),
				$fsId,
				$date,
				StoreLogAction::REMOVED_FROM_SLOT,
				null,
				empty($message) ? null : $message
			);

			// send direct message to the user
			if ($sendKickMessage) {
				$formattedMessage = $this->storeTransactions->createKickMessage($fsId, $storeId, $date, $message);
				$this->messageTransactions->sendMessageToUser($fsId, $this->session->id(), $formattedMessage);
			}
		}
	}

	/**
	 * @OA\Tag(name="pickup")
	 *
	 * @Rest\Patch("stores/{storeId}/pickups/{pickupDate}/{fsId}", requirements={"storeId" = "\d+", "pickupDate" = "[^/]+", "fsId" = "\d+"})
	 * @Rest\RequestParam(name="isConfirmed", nullable=true, default=null)
	 */
	public function editPickupSlotAction(int $storeId, string $pickupDate, int $fsId, ParamFetcher $paramFetcher): Response
	{
		if (!$this->session->id()) {
			throw new UnauthorizedHttpException('');
		}
		if (!$this->storePermissions->mayConfirmPickup($storeId)) {
			throw new AccessDeniedHttpException();
		}

		$date = TimeHelper::parsePickupDate($pickupDate);
		if (is_null($date)) {
			throw new BadRequestHttpException('Invalid date format');
		}

		if ($paramFetcher->get('isConfirmed')) {
			if (!$this->pickupGateway->confirmFetcher($fsId, $storeId, $date)) {
				throw new BadRequestHttpException();
			}
			$this->storeGateway->addStoreLog(
				$storeId,
				$this->session->id(),
				$fsId,
				$date,
				StoreLogAction::SLOT_CONFIRMED
			);
		}

		return $this->handleView($this->view([], 200));
	}

	/**
	 * @OA\Tag(name="pickup")
	 *
	 * @Rest\Patch("stores/{storeId}/pickups/{pickupDate}", requirements={"storeId" = "\d+", "pickupDate" = "[^/]+"})
	 * @Rest\RequestParam(name="totalSlots", nullable=true, default=null)
	 */
	public function editPickupAction(int $storeId, string $pickupDate, ParamFetcher $paramFetcher): Response
	{
		if (!$this->session->id()) {
			throw new UnauthorizedHttpException('');
		}
		if (!$this->storePermissions->mayEditPickups($storeId)) {
			throw new AccessDeniedHttpException();
		}

		$date = TimeHelper::parsePickupDate($pickupDate);
		if (is_null($date)) {
			throw new BadRequestHttpException('Invalid date format');
		}

		if ($date < Carbon::now()) {
			throw new BadRequestHttpException('Cannot modify pickup in the past.');
		}

		$totalSlots = $paramFetcher->get('totalSlots');
		if (!is_null($totalSlots)) {
			if (!$this->storeTransactions->changePickupSlots($storeId, $date, $totalSlots)) {
				throw new BadRequestHttpException();
			}
		}

		return $this->handleView($this->view([], 200));
	}

	/**
	 * @OA\Tag(name="pickup")
	 *
	 * @Rest\Get("stores/{storeId}/pickups", requirements={"storeId" = "\d+"})
	 */
	public function listPickupsAction(int $storeId): Response
	{
		if (!$this->session->id()) {
			throw new UnauthorizedHttpException('');
		}
		if (!$this->storePermissions->maySeePickups($storeId)) {
			throw new AccessDeniedHttpException();
		}
		if (CarbonInterval::hours(Carbon::today()->diffInHours(Carbon::now()))->greaterThanOrEqualTo(CarbonInterval::hours(6))) {
			$fromTime = Carbon::today();
		} else {
			$fromTime = Carbon::today()->subHours(6);
		}

		$pickups = $this->pickupGateway->getPickupSlots($storeId, $fromTime);

		return $this->handleView($this->view([
			'pickups' => $this->enrichPickupSlots($pickups, $storeId)
		]));
	}

	/**
	 * @OA\Tag(name="pickup")
	 *
	 * @Rest\Get("stores/{storeId}/history/{fromDate}/{toDate}", requirements={"storeId" = "\d+", "fromDate" = "[^/]+", "toDate" = "[^/]+"})
	 */
	public function listPickupHistoryAction(int $storeId, string $fromDate, string $toDate): Response
	{
		if (!$this->session->id()) {
			throw new UnauthorizedHttpException('');
		}
		if (!$this->storePermissions->maySeePickupHistory($storeId)) {
			throw new AccessDeniedHttpException();
		}
		// convert date strings into datetime objects
		$from = TimeHelper::parsePickupDate($fromDate);
		$to = TimeHelper::parsePickupDate($toDate);
		if (is_null($from) || is_null($to)) {
			throw new BadRequestHttpException('Invalid date format');
		}
		$from = $from->min(Carbon::now());
		$to = $to->min(Carbon::now());

		$pickups = [[
			'occupiedSlots' => $this->pickupGateway->getPickupHistory($storeId, $from, $to)
		]];

		return $this->handleView($this->view([
			'pickups' => $this->enrichPickupSlots($pickups, $storeId)
		]));
	}

	private function enrichPickupSlots(array $pickups, int $storeId): array
	{
		$team = [];
		foreach ($this->storeGateway->getStoreTeam($storeId) as $user) {
			$team[$user['id']] = RestNormalization::normalizeStoreUser($user);
		}
		foreach ($pickups as &$pickup) {
			foreach ($pickup['occupiedSlots'] as &$slot) {
				if (isset($team[$slot['foodsaverId']])) {
					$slot['profile'] = $team[$slot['foodsaverId']];
				} else {
					$details = $this->foodsaverGateway->getFoodsaver($slot['foodsaverId']);
					$slot['profile'] = RestNormalization::normalizeStoreUser($details);
				}
				unset($slot['foodsaverId']);
			}
		}
		unset($pickup);
		usort($pickups, function ($a, $b) {
			return $a['date']->lt($b['date']) ? -1 : 1;
		});

		return $pickups;
	}

	/**
	 * Get past pickups of a user.
	 * Might be restricted to the last month depending on the permissions.
	 *
	 * @OA\Tag(name="pickup")
	 *
	 * @Rest\Get("pickup/history")
	 * @Rest\QueryParam(name="fsId", nullable=true, default=null)
	 * @Rest\QueryParam(name="page", nullable=false, default=0)
	 * @Rest\QueryParam(name="pageSize", nullable=false, default=50)
	 */
	public function listPastPickupsAction(ParamFetcher $paramFetcher): Response
	{
		if (!$this->session->id()) {
			throw new UnauthorizedHttpException('');
		}

		$fsId = (int)($paramFetcher->get('fsId') ?? $this->session->id());
		$page = (int)$paramFetcher->get('page');
		$pageSize = (int)$paramFetcher->get('pageSize');

		if (!$this->session->id() || !$this->profilePermissions->maySeePickups($fsId)) {
			throw new AccessDeniedHttpException();
		}

		$maySeeFullHistory = $this->profilePermissions->maySeeAllPickups($fsId);

		$pickups = $this->pickupGateway->getPastPickups($fsId, $page, $pageSize, $maySeeFullHistory);

		$pickups = array_map(fn ($pickup) => [
			'date' => RestNormalization::normalizeDate($pickup['timestamp']),
			'store' => [
				'id' => $pickup['store_id'],
				'name' => $pickup['store_name'],
			],
			'confirmed' => $pickup['confirmed'],
			'slots' => [
				'occupied' => array_map(
					fn ($id, $name, $avatar, $confirmed) => [
						'id' => (int)$id,
						'name' => $name,
						'avatar' => $avatar == '' ? null : $avatar,
						'confirmed' => (int)$confirmed,
					],
					str_getcsv($pickup['fs_ids']),
					str_getcsv($pickup['fs_names'], ',', '\''),
					str_getcsv($pickup['fs_avatars']),
					str_getcsv($pickup['slot_confimations'])
				)
			]
		], $pickups);

		return $this->handleView($this->view($pickups));
	}

	/**
	 * Get all future pickups a user has registered.
	 *
	 * @OA\Tag(name="pickup")
	 *
	 * @Rest\Get("pickup/registered")
	 * @Rest\QueryParam(name="fsId", nullable=true, default=null)
	 */
	public function listRegisteredPickupsAction(ParamFetcher $paramFetcher): Response
	{
		if (!$this->session->id()) {
			throw new UnauthorizedHttpException('');
		}

		$fsId = (int)($paramFetcher->get('fsId') ?? $this->session->id());

		if (!$this->session->id() || !$this->profilePermissions->maySeePickups($fsId)) {
			throw new AccessDeniedHttpException();
		}

		$pickups = $this->pickupGateway->getNextPickups($fsId);

		$pickups = array_map(fn ($pickup) => [
			'date' => RestNormalization::normalizeDate($pickup['timestamp']),
			'store' => [
				'id' => $pickup['store_id'],
				'name' => $pickup['store_name'],
			],
			'confirmed' => $pickup['confirmed'],
			'slots' => [
				'occupied' => array_map(
					fn ($id, $name, $avatar, $confirmed) => [
						'id' => (int)$id,
						'name' => $name,
						'avatar' => $avatar == '' ? null : $avatar,
						'confirmed' => (int)$confirmed,
					],
					str_getcsv($pickup['fs_ids']),
					str_getcsv($pickup['fs_names'], ',', '\''),
					str_getcsv($pickup['fs_avatars']),
					str_getcsv($pickup['slot_confimations'])
				),
				'max' => $pickup['max_fetchers'],
			]
		], $pickups);

		return $this->handleView($this->view($pickups));
	}

	/**
	 * Get all pickup options a user has, including already registered slots.
	 *
	 * @OA\Response(response="200", description="Success")
	 * @OA\Response(response="403", description="Insufficient permissions")
	 * @OA\Tag(name="pickup")
	 *
	 * @Rest\Get("pickup/options")
	 * @Rest\QueryParam(name="page", nullable=false, default=0)
	 * @Rest\QueryParam(name="pageSize", nullable=false, default=50)
	 */
	public function listPickupOptionsAction(ParamFetcher $paramFetcher): Response
	{
		if (!$this->session->id()) {
			throw new UnauthorizedHttpException('');
		}

		$page = (int)$paramFetcher->get('page');
		$pageSize = (int)$paramFetcher->get('pageSize');
		$id = $this->session->id();
		if (!$this->session->may() || !$this->storePermissions->maySeePickupOptions($id)) {
			throw new AccessDeniedHttpException();
		}

		//fetch stores and pickup slots:
		$pickupOptions = [];
		$pickupSlots = null;

		$isConfirmed = function ($id, $users) {
			foreach ($users as $u) {
				if ($u['profile']['id'] === $id) {
					return $u['isConfirmed'];
				}
			}

			return null;
		};

		foreach ($this->storeGateway->getStores($id) as $store) {
			$pickupSlots = $this->enrichPickupSlots(
				$this->pickupGateway->getPickupSlots($store['id']),
				$store['id']
			);

			$pickupOptions = array_merge($pickupOptions, array_map(
				fn ($slot) => [
					'date' => RestNormalization::normalizeDate(strtotime($slot['date'])),
					'store' => $store,
					'confirmed' => $isConfirmed($id, $slot['occupiedSlots']),
					'slots' => [
						'occupied' => array_map(
							fn ($user) => [
								'id' => $user['profile']['id'],
								'name' => $user['profile']['name'],
								'avatar' => $user['profile']['avatar'],
								'confirmed' => $user['isConfirmed'],
							],
							$slot['occupiedSlots']
						),
						'max' => $slot['totalSlots'],
					],
				],
				$pickupSlots
			));
		}

		// Filtering (exclude completely filled slots without the user in them)
		$pickupOptions = array_values(array_filter(
			$pickupOptions,
			fn ($obj) => count($obj['slots']['occupied']) < $obj['slots']['max'] || !is_null($obj['confirmed'])
		));

		usort($pickupOptions, fn ($a, $b) => strtotime($a['date']) <=> strtotime($b['date']));

		if ($page != -1 && $pageSize != -1) {
			$pickupOptions = array_slice($pickupOptions, $page * $pageSize, $pageSize);
		}

		return $this->handleView($this->view($pickupOptions));
	}
}
