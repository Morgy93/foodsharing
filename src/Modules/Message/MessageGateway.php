<?php
/**
 * standard gateway for the message component providing database queries. Available in other classes through symfony's autowiring.
 *
 * @see https://symfony.com/doc/current/service_container/autowiring.html Defining Services Dependencies Automatically (Autowiring)
 * User: pmayd
 * Created: 2019-01-13
 * Last Change: 2019-01-14
 */

namespace Foodsharing\Modules\Message;

use Foodsharing\Modules\Core\BaseGateway;

final class MessageGateway extends BaseGateway
{
	public function getConversationName(int $conversationId): ?string
	{
		return $this->db->fetchValueByCriteria('fs_conversation', 'name', ['id' => $conversationId]);
	}

	public function getConversationMemberNamesExcept(int $conversationId, int $excludeId): array
	{
		$members = $this->db->fetchAll(
			'SELECT fs.name FROM fs_foodsaver_has_conversation fc, fs_foodsaver fs WHERE fs.id = fc.foodsaver_id AND fc.conversation_id = :id AND fs.deleted_at IS NULL AND fs.id <> :excludeId',
			['id' => $conversationId,
				'excludeId' => $excludeId]
		);

		return array_map(function ($member) { return $member['name']; }, $members);
	}

	public function getConversationMessages(int $conversation_id, int $limit = 20, int $offset = 0): array
	{
		return $this->db->fetchAll('
			SELECT
				m.id,
				fs.`id` AS fs_id,
				fs.name AS fs_name,
				fs.photo AS fs_photo,
				m.`body`,
				m.`time`

			FROM
				`fs_msg` m,
				`fs_foodsaver` fs

			WHERE
				m.foodsaver_id = fs.id

			AND
				m.conversation_id = :id

			ORDER BY
				m.`time` DESC

			LIMIT :offset, :limit
		', [
			'id' => $conversation_id,
			'offset' => $offset,
			'limit' => $limit,
		]);
	}

	/**
	 * Renames an Conversation.
	 */
	public function renameConversation($cid, $name): bool
	{
		return $this->db->update('fs_conversation', ['name' => strip_tags($name)], ['id' => (int)$cid]);
	}

	public function conversationLocked($cid)
	{
		return $this->db->fetchValueByCriteria('fs_conversation', 'locked', ['id' => (int)$cid]);
	}

	public function listConversationUpdates($conv_ids)
	{
		if ($return = $this->db->fetchAll('
			SELECT
				`id` AS id,
				`last` AS time,
				`last_message` AS body,
				`member`

			FROM
				`fs_conversation`

			WHERE
				`id` IN(' . implode(',', array_map('intval', $conv_ids)) . ')
		')
		) {
			foreach ($return as $i => $iValue) {
				$return[$i]['member'] = unserialize($return[$i]['member']);
			}

			return $return;
		}

		return false;
	}

	public function loadMore(int $conversation_id, int $last_message_id, int $limit = 20): array
	{
		return $this->db->fetchAll('
			SELECT
				m.id,
				fs.`id` AS fs_id,
				fs.name AS fs_name,
				fs.photo AS fs_photo,
				m.`body`,
				m.`time`

			FROM
				`fs_msg` m,
				`fs_foodsaver` fs

			WHERE
				m.foodsaver_id = fs.id

			AND
				m.conversation_id = :conf_id

			AND
				m.id < :last_msg_id

			ORDER BY
				m.`time` DESC

			LIMIT 0,:limit
		', [':conv_id' => $conversation_id, ':last_msg_id' => $last_message_id, ':limit' => $limit]);
	}

	public function getLastMessages($conv_id, $last_msg_id): array
	{
		return $this->db->fetchAll('
			SELECT
				m.id,
				fs.`id` AS fs_id,
				fs.name AS fs_name,
				fs.photo AS fs_photo,
				m.`body`,
				m.`time`

			FROM
				`fs_msg` m,
				`fs_foodsaver` fs

			WHERE
				m.foodsaver_id = fs.id

			AND
				m.conversation_id = :conf_id

			AND
				m.id > :last_msg_id

			ORDER BY
				m.`time` ASC
		', [':conv_id' => (int)$conv_id, ':last_msg_id' => (int)$last_msg_id]);
	}
}
