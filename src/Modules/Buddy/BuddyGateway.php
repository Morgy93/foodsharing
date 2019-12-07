<?php

namespace Foodsharing\Modules\Buddy;

use Foodsharing\Modules\Core\BaseGateway;

class BuddyGateway extends BaseGateway
{
	public function listBuddies($fsId): array
	{
		$stm = '
			SELECT 	fs.`id`,
					fs.name,
					fs.nachname,
					fs.photo
			
			FROM 	fs_foodsaver fs,
					fs_buddy b
		
			WHERE 	b.buddy_id = fs.id
			AND 	b.foodsaver_id = :foodsaver_id
			AND 	b.confirmed = 1
		';

		return $this->db->fetchAll($stm, [':foodsaver_id' => $fsId]);
	}

	public function listBuddyIds($fsId): array
	{
		return $this->db->fetchAllValuesByCriteria('fs_buddy', 'buddy_id', ['foodsaver_id' => $fsId, 'confirmed' => 1]);
	}

	public function removeRequest($buddyId, $fsId): void
	{
		$this->db->delete('fs_buddy', ['foodsaver_id' => (int)$buddyId, 'buddy_id' => (int)$fsId]);
	}

	public function buddyRequestedMe($buddyId, $fsId): bool
	{
		if ($this->db->exists('fs_buddy', ['foodsaver_id' => (int)$buddyId, 'buddy_id' => (int)$fsId])) {
			return true;
		}

		return false;
	}

	public function buddyRequest(int $buddyId, int $foodsaverId): bool
	{
		$this->db->insertOrUpdate('fs_buddy', [
			'foodsaver_id' => $foodsaverId,
			'buddy_id' => $buddyId,
			'confirmed' => 0
		]);

		return true;
	}

	public function confirmBuddy(int $buddyId, int $foodsaverId): void
	{
		$this->db->insertOrUpdate('fs_buddy', [
			'foodsaver_id' => $foodsaverId,
			'buddy_id' => $buddyId,
			'confirmed' => 1
		]);
		$this->db->insertOrUpdate('fs_buddy', [
			'foodsaver_id' => $buddyId,
			'buddy_id' => $foodsaverId,
			'confirmed' => 1
		]);
	}
}
