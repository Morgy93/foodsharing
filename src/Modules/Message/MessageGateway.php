<?php

namespace Foodsharing\Modules\Message;

use Carbon\Carbon;
use Foodsharing\Modules\Core\BaseGateway;

final class MessageGateway extends BaseGateway
{
	public function mayConversation($fsId, $conversationId): bool
	{
		return $this->db->exists('fs_foodsaver_has_conversation', ['foodsaver_id' => $fsId, 'conversation_id' => $conversationId]);
	}

	public function createConversation(array $fsIds, bool $locked = false): int
	{
		$this->db->beginTransaction();
		$conversationId = $this->db->insert('fs_conversation', [
			'locked' => $locked ? 1 : 0
		]);
		foreach ($fsIds as $fsId) {
			$this->db->insert('fs_foodsaver_has_conversation', [
				'foodsaver_id' => $fsId,
				'conversation_id' => $conversationId,
				'unread' => 0
			]);
		}
		/* todo: would expect foreign key constraints to fail when a conversation with non-existing users is added.
		That constraint is not in place and previous behaviour of messages did not check either, so keep it for now... */
		$this->db->commit();

		return $conversationId;
	}

	public function getOrCreateConversation(array $fsIds)
	{
		/* need to fetch a conversation with the exact set of participants. As there is no direct way to do this,
		   the approach is to
		   1. get the set of possible conversations by selecting all conversations where one of the users is a participant. Take care, not to select "locked" conversations, which are special and have a separated team management logic.
		   2. comparing the participant lists of the conversations from 1 with the participant list we want to have
		   3. in case 2 did not yield a result, creating a new conversation.
		*/

		sort($fsIds);
		$possibleConversations = $this->db->fetchAllValues(
			'SELECT hc.conversation_id
			FROM fs_foodsaver_has_conversation hc 
			LEFT JOIN fs_conversation c on hc.conversation_id = c.id
			WHERE hc.foodsaver_id = :fsId AND
			c.locked = 0',
			[':fsId' => $fsIds[0]]
		);

		if ($possibleConversations) {
			$idString = implode(':', array_map('intval', $fsIds));
			$results = $this->db->fetchAllValues('
			SELECT
                  conversation_id,
                  GROUP_CONCAT(foodsaver_id ORDER BY foodsaver_id SEPARATOR ":") AS idstring
        
                FROM
                  fs_foodsaver_has_conversation
        
                WHERE
                  conversation_id IN (' . $this->db->generatePlaceholders(count($possibleConversations)) . ')
        
                GROUP BY
                  conversation_id
        
                HAVING
                  idstring = ?',
			array_merge($possibleConversations, [$idString]));
			if (count($results) > 1) {
				trigger_error('Found multiple conversations with ID set ' . $idString);

				return $results[0];
			}
			if ($results) {
				return $results[0];
			}
		}

		/*
		 * 3. No conversation found, create one
		*/

		return $this->createConversation($fsIds);
	}

	public function getConversationName(int $conversationId): ?string
	{
		return $this->db->fetchValueByCriteria('fs_conversation', 'name', ['id' => $conversationId]);
	}

	public function getConversationMessages(int $conversation_id, int $limit = 20, ?int $olderThanId = null): array
	{
		$offsetStr = '';
		$queryParams = [
			'id' => $conversation_id,
			'limit' => $limit,
		];
		if ($olderThanId !== null) {
			$offsetStr = 'AND m.id < :olderThanId';
			$queryParams['olderThanId'] = $olderThanId;
		}

		$res = $this->db->fetchAll('
			SELECT
				m.id,
				fs.`id` AS fs_id,
				fs.name AS fs_name,
				fs.photo AS fs_photo,
				m.`body`,
				m.`time`,
				m.`is_htmlentity_encoded`
			FROM
				`fs_msg` m,
				`fs_foodsaver` fs
			WHERE
				m.foodsaver_id = fs.id
			AND
				m.conversation_id = :id
			' . $offsetStr . '
			ORDER BY
				m.`time` DESC

			LIMIT :limit
		', $queryParams);
		$res = array_map(function ($e) {
			if ($e['is_htmlentity_encoded']) {
				$e['body'] = html_entity_decode($e['body']);
			}

			return $e;
		}, $res);

		return $res;
	}

	/**
	 * Renames an Conversation.
	 */
	public function renameConversation(int $cid, string $name): bool
	{
		return $this->db->update('fs_conversation', ['name' => $name], ['id' => $cid]);
	}

	public function isConversationLocked(int $cid)
	{
		return $this->db->fetchValueByCriteria('fs_conversation', 'locked', ['id' => $cid]);
	}

	/**
	 * Method returns an array of all conversations a given user is part of.
	 *
	 * @param int $limit
	 * @param int $offset
	 *
	 * @return array
	 */
	public function listConversationsForUser(int $fsId, int $limit = null, int $offset = 0)
	{
		$paginate = null;
		if ($limit !== null) {
			$paginate = ' LIMIT :offset, :limit';
		}

		$query = '
			SELECT
				c.`id`,
				c.`last` AS last_message_at,
				c.`last_message`,
				c.`last_foodsaver_id` AS last_message_author_id,
				hc.unread as has_unread_messages,
				c.name,
				c.last_message_is_htmlentity_encoded

			FROM
				fs_conversation c,
				`fs_foodsaver_has_conversation` hc

			WHERE
				hc.conversation_id = c.id

			AND
				hc.foodsaver_id = :fsId
				
			AND
			    c.last_message <> ""

			ORDER BY
				hc.unread DESC,
				c.`last` DESC';
		if ($paginate) {
			$conversations = $this->db->fetchAll($query . $paginate, [':fsId' => $fsId, ':offset' => $offset, ':limit' => $limit]);
		} else {
			$conversations = $this->db->fetchAll($query, [':fsId' => $fsId]);
		}

		array_walk($conversations, function (&$c) {
			$c['last_message_at'] = new \DateTime($c['last_message_at']);
			$c['has_unread_messages'] = (bool)$c['has_unread_messages'];
			if ($c['last_message_is_htmlentity_encoded'] == 1) {
				$c['last_message'] = html_entity_decode($c['last_message']);
			}
		});

		return $conversations;
	}

	public function listConversationMembersWithProfile(int $conversationId): array
	{
		return $this->db->fetchAll('		
			SELECT
				fs.id,
				fs.name,
				fs.photo,
				fs.email,
				fs.geschlecht AS gender,
				fs.infomail_message

			FROM
                `fs_foodsaver_has_conversation` hc
                
			INNER JOIN
				`fs_foodsaver` fs ON fs.id = hc.foodsaver_id

			WHERE
				hc.conversation_id = :conversationId AND
				fs.deleted_at IS NULL
		', [':conversationId' => $conversationId]);
	}

	private function getProfileForUsers(array $fsIds): array
	{
		return $this->db->fetchAllByCriteria(
			'fs_foodsaver',
			['id', 'name', 'photo', 'sleep_status'],
			['id' => $fsIds]);
	}

	private function getMembersForConversations(array $cids): array
	{
		if ($cids) {
			$placeholders = $this->db->generatePlaceholders(count($cids));

			$results = $this->db->fetchAll('
					SELECT
						conversation_id,
						GROUP_CONCAT(foodsaver_id) as members
					FROM fs_foodsaver_has_conversation
					WHERE conversation_id IN (' . $placeholders . ') GROUP BY conversation_id',
				$cids
			);
			$indexedResult = [];
			foreach ($results as $result) {
				$indexedResult[$result['conversation_id']] = explode(',', $result['members']);
			}

			return $indexedResult;
		}

		return [];
	}

	private function flatten(array $array): array
	{
		$return = array();
		array_walk_recursive($array, function ($a) use (&$return) { $return[] = $a; });

		return $return;
	}

	public function listConversationsForUserIncludeProfiles(int $fsId, int $limit = null, int $offset = 0): array
	{
		$conversations = $this->listConversationsForUser($fsId, $limit, $offset);
		$cids = array_column($conversations, 'id');
		$members = $this->getMembersForConversations($cids);
		$allUserIds = array_unique($this->flatten($members));
		$profiles = $this->getProfileForUsers($allUserIds);
		$indexedProfiles = [];
		foreach ($profiles as $profile) {
			$indexedProfiles[$profile['id']] = $profile;
		}
		array_walk($conversations, function (&$c) use ($members, $indexedProfiles) {
			$res = [];
			foreach ($members[$c['id']] as $member) {
				$res[] = $indexedProfiles[$member];
			}
			$c['members'] = $res;
		});

		return $conversations;
	}

	private function updateLastConversationMessage(int $conversationId, int $lastMessageId, string $lastMessageBody, int $lastMessageAuthor, Carbon $lastMessageAt): void
	{
		$this->db->update('fs_conversation',
			[
				'last' => $lastMessageAt->toDateTimeString(),
				'last_foodsaver_id' => $lastMessageAuthor,
				'last_message' => $lastMessageBody,
				'last_message_id' => $lastMessageId,
				'last_message_is_htmlentity_encoded' => 0
			],
			['id' => $conversationId]
		);
	}

	private function markAsUnread(int $conversationId, int $exceptFsId): void
	{
		$this->db->update('fs_foodsaver_has_conversation',
			['unread' => 1],
			[
				'conversation_id' => $conversationId,
				'foodsaver_id !=' => $exceptFsId
			]);
	}

	public function markAsRead(int $conversationId, int $fsId): void
	{
		$this->db->update('fs_foodsaver_has_conversation',
			['unread' => 0],
			[
				'foodsaver_id' => $fsId,
				'conversation_id' => $conversationId]
		);
	}

	public function addMessage(int $conversationId, int $senderId, string $body, Carbon $sentAt = null): int
	{
		if ($sentAt === null) {
			$sentAt = Carbon::now();
		}
		$messageId = $this->db->insert('fs_msg',
			[
				'conversation_id' => $conversationId,
				'foodsaver_id' => $senderId,
				'body' => $body,
				'time' => $sentAt->toDateTimeString(),
				'is_htmlentity_encoded' => 0
			]);
		$this->markAsUnread($conversationId, $senderId);
		$this->updateLastConversationMessage($conversationId, $messageId, $body, $senderId, $sentAt);

		return $messageId;
	}

	public function deleteUserFromConversation(int $conversationId, int $userId): bool
	{
		return $this->db->delete('fs_foodsaver_has_conversation', [
			'conversation_id' => $conversationId,
			'foodsaver_id' => $userId
		]) === 1;
	}

	public function addUserToConversation(int $conversationId, int $userId): bool
	{
		return $this->db->insert('fs_foodsaver_has_conversation', [
			'conversation_id' => $conversationId,
			'foodsaver_id' => $userId,
			'unread' => 0
		], ['ignore' => true]) > 0;
	}

	public function setConversationMembers(int $conversationId, array $userIds): void
	{
		if (!$userIds) {
			/* Empty user list gets special handling */
			$this->db->delete('fs_foodsaver_has_conversation', [
				'conversation_id' => $conversationId
			]);
		} else {
			$this->db->beginTransaction();
			$this->db->execute('
			DELETE FROM fs_foodsaver_has_conversation
			WHERE foodsaver_id NOT IN (' . $this->db->generatePlaceholders(count($userIds)) . ')
			AND conversation_id = ?
			', array_merge($userIds, [$conversationId]));

			/* TODO: database layer doesn't support helpers for bulk insert */
			foreach ($userIds as $userId) {
				$this->db->insert('fs_foodsaver_has_conversation', [
					'conversation_id' => $conversationId,
					'foodsaver_id' => $userId,
					'unread' => 0
				]);
			}
			$this->db->commit();
		}
	}

	/* checks if the conversation has a member that is not deleted (to expunge the conversation otherwise) */
	public function conversationHasRealMembers(int $conversationId): bool
	{
		return $this->db->fetchValue('
		SELECT COUNT(*)
		FROM fs_foodsaver_has_conversation hc
		INNER JOIN fs_foodsaver fs ON fs.id = hc.foodsaver_id
		WHERE hc.conversation_id = :conversationId
		AND fs.deleted_at IS NULL
		', [':conversationId' => $conversationId]) >= 1;
	}

	public function deleteConversation(int $conversationId): void
	{
		$this->db->beginTransaction();
		$this->db->delete('fs_foodsaver_has_conversation', ['conversation_id' => $conversationId]);
		$this->db->delete('fs_msg', ['conversation_id' => $conversationId]);
		$this->db->delete('fs_conversation', ['id' => $conversationId]);
		$this->db->commit();
	}
}
