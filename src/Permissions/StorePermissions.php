<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Store\TeamStatus;
use Foodsharing\Modules\Store\StoreGateway;

class StorePermissions
{
	private StoreGateway $storeGateway;
	private Session $session;

	public function __construct(
		StoreGateway $storeGateway,
		Session $session
	) {
		$this->storeGateway = $storeGateway;
		$this->session = $session;
	}

	public function mayJoinStoreRequest(int $storeId): bool
	{
		$fsId = $this->session->id();
		if (!$fsId) {
			return false;
		}

		$storeTeamStatus = $this->storeGateway->getStoreTeamStatus($storeId);

		// store open?
		if (!in_array($storeTeamStatus, [TeamStatus::OPEN, TeamStatus::OPEN_SEARCHING])) {
			return false;
		}

		// already in team?
		if ($this->storeGateway->getUserTeamStatus($fsId, $storeId) !== \Foodsharing\Modules\Store\TeamStatus::NoMember) {
			return false;
		}

		return true;
	}

	public function mayAccessStore(int $storeId): bool
	{
		$fsId = $this->session->id();
		if (!$fsId) {
			return false;
		}

		if ($this->session->isOrgaTeam()) {
			return true;
		}
		if ($this->storeGateway->getUserTeamStatus($fsId, $storeId) >= \Foodsharing\Modules\Store\TeamStatus::WaitingList) {
			return true;
		}

		$storeRegion = $this->storeGateway->getStoreRegionId($storeId);
		if ($this->session->isAdminFor($storeRegion)) {
			return true;
		}

		return false;
	}

	public function mayReadStoreWall(int $storeId): bool
	{
		$fsId = $this->session->id();
		if (!$fsId) {
			return false;
		}

		if ($this->session->isOrgaTeam()) {
			return true;
		}
		if ($this->storeGateway->getUserTeamStatus($fsId, $storeId) >= \Foodsharing\Modules\Store\TeamStatus::Member) {
			return true;
		}

		$storeRegion = $this->storeGateway->getStoreRegionId($storeId);
		if ($this->session->isAdminFor($storeRegion)) {
			return true;
		}

		return false;
	}

	public function mayWriteStoreWall(int $storeId): bool
	{
		return $this->mayReadStoreWall($storeId);
	}

	public function mayDeleteStoreWallPost(int $postId): bool
	{
		return $this->session->may('orga')
			|| $this->storeGateway->getStoreComment($postId)['foodsaver_id'] === $this->session->id();
	}

	public function mayCreateStore(): bool
	{
		return $this->session->may('bieb');
	}

	public function mayEditStore(int $storeId): bool
	{
		$fsId = $this->session->id();
		if (!$fsId) {
			return false;
		}

		if (!$this->session->may('bieb')) {
			return false;
		}

		if ($this->session->isOrgaTeam()) {
			return true;
		}
		if ($this->storeGateway->getUserTeamStatus($fsId, $storeId) === \Foodsharing\Modules\Store\TeamStatus::Coordinator) {
			return true;
		}
		$storeRegion = $this->storeGateway->getStoreRegionId($storeId);
		if ($this->session->isAdminFor($storeRegion)) {
			return true;
		}

		return false;
	}

	public function mayEditStoreTeam(int $storeId): bool
	{
		return $this->mayEditStore($storeId);
	}

	public function mayRemovePickupUser(int $storeId, int $fsId): bool
	{
		if ($fsId === $this->session->id()) {
			return true;
		}

		if ($this->mayEditPickups($storeId)) {
			return true;
		}

		return false;
	}

	public function mayConfirmPickup(int $storeId): bool
	{
		return $this->mayEditPickups($storeId);
	}

	public function mayEditPickups(int $storeId): bool
	{
		return $this->mayEditStore($storeId);
	}

	public function mayAcceptRequests(int $storeId): bool
	{
		return $this->mayEditStore($storeId);
	}

	public function mayAddPickup(int $storeId): bool
	{
		return $this->mayEditPickups($storeId);
	}

	public function mayDeletePickup(int $storeId): bool
	{
		return $this->mayEditPickups($storeId);
	}

	public function maySeePickupHistory(int $storeId): bool
	{
		return $this->mayEditStore($storeId);
	}

	public function mayDoPickup(int $storeId): bool
	{
		if (!$this->session->isVerified()) {
			return false;
		}

		if (!$this->mayReadStoreWall($storeId)) {
			return false;
		}

		return true;
	}

	public function maySeePickups(int $storeId): bool
	{
		return $this->mayDoPickup($storeId);
	}

	public function maySeePhoneNumbers(int $storeId): bool
	{
		return $this->mayDoPickup($storeId);
	}

	public function mayChatWithRegularTeam(array $store): bool
	{
		return (!$store['jumper'] || $store['verantwortlich'])
			&& $store['team_conversation_id'] !== null;
	}

	public function mayChatWithJumperWaitingTeam(array $store): bool
	{
		return $store['verantwortlich'] && $store['springer_conversation_id'] !== null;
	}
}
