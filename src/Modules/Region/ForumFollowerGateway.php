<?php

namespace Foodsharing\Modules\Region;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\DBConstants\Info\FollowStatus;
use Foodsharing\Modules\Core\DBConstants\Info\InfoType;

class ForumFollowerGateway extends BaseGateway
{
	public function getThreadEmailFollower(int $fsId, int $threadId): array
	{
		return $this->db->fetchAll('
			SELECT 	fs.name,
					fs.geschlecht,
					fs.email

			FROM 	fs_foodsaver fs,
					fs_theme_follower tf
			WHERE 	tf.foodsaver_id = fs.id
			AND 	tf.theme_id = :threadId
			AND 	tf.foodsaver_id != :fsId
			AND		fs.deleted_at IS NULL
			AND		tf.infotype = :infotype_email
		', [
			':threadId' => $threadId,
			':fsId' => $fsId,
			':infotype_email' => InfoType::EMAIL,
		]);
	}

	public function getEmailSubscribedThreadsForUser(int $fsId): array
	{
		return $this->db->fetchAll('
			SELECT
				th.id,
				th.name,
				tf.infotype

			FROM
				`fs_theme_follower` tf
				LEFT JOIN `fs_theme` th
				ON tf.theme_id = th.id

			WHERE
				tf.foodsaver_id = :fsId
			AND
				tf.infotype = 1
		', [':fsId' => $fsId]);
	}

	public function getThreadBellFollower(int $threadId, int $fsId): array
	{
		return $this->db->fetchAll('
			SELECT 	DISTINCT fs.id AS id

			FROM 	fs_foodsaver fs,
					fs_theme_follower tf
			WHERE 	tf.foodsaver_id = fs.id
			AND 	tf.theme_id = :threadId
			AND		fs.deleted_at IS NULL
			AND		tf.bell_notification = :followstatus_enabled
			AND		fs.deleted_at IS NULL
			AND		fs.id != :fsId
		', [
			':threadId' => $threadId,
			':fsId' => $fsId,
			':followstatus_enabled' => FollowStatus::ENABLED,
		]);
	}

	public function isFollowingEmail(?int $fsId, int $threadId): bool
	{
		return $this->db->exists(
			'fs_theme_follower',
			['theme_id' => $threadId, 'foodsaver_id' => $fsId, 'infotype' => InfoType::EMAIL]
		);
	}

	public function isFollowingBell(?int $fsId, int $threadId): bool
	{
		return $this->db->exists(
			'fs_theme_follower',
			['theme_id' => $threadId, 'foodsaver_id' => $fsId, 'bell_notification' => FollowStatus::ENABLED]
		);
	}

	public function followThreadByEmail(?int $fsId, int $threadId): int
	{
		return $this->db->insertOrUpdate(
			'fs_theme_follower',
			['foodsaver_id' => $fsId, 'theme_id' => $threadId, 'infotype' => InfoType::EMAIL]
		);
	}

	public function updateInfoType(int $fsId, int $threadId, int $infoType): int
	{
		return $this->db->update(
			'fs_theme_follower',
			['infotype' => $infoType],
			[
				'foodsaver_id' => $fsId,
				'theme_id' => $threadId,
			]
		);
	}

	public function followThreadByBell(?int $fsId, int $threadId): int
	{
		return $this->db->insertOrUpdate(
			'fs_theme_follower',
			['foodsaver_id' => $fsId, 'theme_id' => $threadId, 'bell_notification' => FollowStatus::ENABLED]
		);
	}

	public function unfollowThreadByEmail(?int $fsId, int $threadId): int
	{
		return $this->db->insertOrUpdate(
			'fs_theme_follower',
			['foodsaver_id' => $fsId, 'theme_id' => $threadId, 'infotype' => InfoType::NONE]
		);
	}

	public function unfollowThreadByBell(?int $fsId, int $threadId): int
	{
		return $this->db->insertOrUpdate(
			'fs_theme_follower',
			['foodsaver_id' => $fsId, 'theme_id' => $threadId, 'bell_notification' => FollowStatus::DISABLED]
		);
	}

	/**
	 * Removes the forum subscription for one foodsaver from the region or group.
	 *
	 * @param int $regionId id of the group
	 * @param int $foodsaverId id of the foodsaver
	 *
	 * @throws \Exception
	 */
	public function deleteForumSubscription(int $regionId, int $foodsaverId): void
	{
		$themeIds = $this->db->fetchAllValuesByCriteria('fs_bezirk_has_theme', 'theme_id', ['bezirk_id' => $regionId]);
		$this->db->delete('fs_theme_follower', ['theme_id' => $themeIds, 'foodsaver_id' => $foodsaverId]);
	}

	/**
	 * Removes the forum subscriptions for all deleted members or ambassadors in the region or group.
	 *
	 * @param int $regionId id of the group
	 * @param array $remainingMemberIds list of remaining members, or null to remove all
	 * @param bool $useAmbassadors if the ambassador table should be used
	 */
	public function deleteForumSubscriptions(int $regionId, array $remainingMemberIds, bool $useAmbassadors): void
	{
		$foodsaverTableName = $useAmbassadors ? 'fs_botschafter' : 'fs_foodsaver_has_bezirk';
		$threadIds = $this->db->fetchAllValuesByCriteria('fs_bezirk_has_theme', 'theme_id', ['bezirk_id' => $regionId]);

		if ($threadIds && !empty($threadIds)) {
			$query = '
				DELETE	tf.*
				FROM		`fs_theme_follower` tf
				JOIN		`fs_bezirk_has_theme` ht
				ON			ht.`theme_id` = tf.`theme_id`
				LEFT JOIN	`' . $foodsaverTableName . '` b
				ON			b.`bezirk_id` = ht.`bezirk_id`
				AND			b.`foodsaver_id` = tf.`foodsaver_id`
				WHERE		tf.`theme_id` IN (' . implode(',', array_map('intval', $threadIds)) . ')
			';
			if ($remainingMemberIds && !empty($remainingMemberIds)) {
				$query .= 'AND	tf.`foodsaver_id` NOT IN(' . implode(',', array_map('intval', $remainingMemberIds)) . ')';
			}

			$this->db->execute($query);
		}
	}

	/**
	 * Recreates the default behaviour of pre may 2020 release by adding bell notifications for everybody who did not set/disable
	 * email notifications for a certain thread.
	 *
	 * @return int number of inserted entries
	 */
	public function createFollowerEntriesForExistingThreads(): int
	{
		$query = 'INSERT IGNORE INTO fs_theme_follower (foodsaver_id, theme_id, infotype, bell_notification)
				SELECT foodsaver_id, theme_id, 0, 1 FROM fs_theme_post';

		return $this->db->execute($query)->rowCount();
	}
}
