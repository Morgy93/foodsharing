<?php

namespace Foodsharing\Modules\Region;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\DBConstants\Info\InfoType;

class ForumFollowerGateway extends BaseGateway
{
	public function getThreadFollower($fs_id, $thread_id)
	{
		return $this->db->fetchAll('
			SELECT 	fs.name,
					fs.geschlecht,
					fs.email

			FROM 	fs_foodsaver fs,
					fs_theme_follower tf
			WHERE 	tf.foodsaver_id = fs.id
			AND 	tf.theme_id = :theme_id
			AND 	tf.foodsaver_id != :fs_id
			AND		fs.deleted_at IS NULL
		', ['theme_id' => $thread_id, 'fs_id' => $fs_id]);
	}

	public function getForumThreads(int $fsId): array
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
		', [':fsId' => $fsId]);
	}

	public function isFollowing($fsId, $threadId)
	{
		return $this->db->exists(
			'fs_theme_follower',
			['theme_id' => $threadId, 'foodsaver_id' => $fsId]
		);
	}

	public function followThread($fs_id, $thread_id)
	{
		return $this->db->insertIgnore(
			'fs_theme_follower',
			['foodsaver_id' => $fs_id, 'theme_id' => $thread_id, 'infotype' => InfoType::EMAIL]
		);
	}

	public function updateInfoType(int $fsId, int $themeId, int $infoType): int
	{
		return $this->db->update(
			'fs_theme_follower',
			['infotype' => $infoType],
			[
				'foodsaver_id' => $fsId,
				'theme_id' => $themeId
			]
		);
	}

	public function unfollowThread(int $fsId, int $threadId): int
	{
		return $this->db->delete(
			'fs_theme_follower',
			[
				'foodsaver_id' => $fsId,
				'theme_id' => $threadId
			]
		);
	}

	public function unfollowThreads(int $fsId, array $threadIds): int
	{
		return $this->db->delete(
			'fs_theme_follower',
			[
				'foodsaver_id' => $fsId,
				'theme_id' => $threadIds
			]
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
		$themeIds = $this->db->fetchAllValuesByCriteria('fs_bezirk_has_theme', 'theme_id', ['bezirk_id' => $regionId]);

		if ($themeIds && !empty($themeIds)) {
			$query = '
				DELETE	tf.*
				FROM		`fs_theme_follower` tf
				JOIN		`fs_bezirk_has_theme` ht
				ON			ht.`theme_id` = tf.`theme_id`
				LEFT JOIN	`' . $foodsaverTableName . '` b
				ON			b.`bezirk_id` = ht.`bezirk_id`
				AND			b.`foodsaver_id` = tf.`foodsaver_id`
				WHERE		tf.`theme_id` IN (' . implode(',', array_map('intval', $themeIds)) . ')
			';
			if ($remainingMemberIds && !empty($remainingMemberIds)) {
				$query .= 'AND	tf.`foodsaver_id` NOT IN(' . implode(',', array_map('intval', $remainingMemberIds)) . ')';
			}

			$this->db->execute($query);
		}
	}
}
