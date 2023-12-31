<?php

namespace Foodsharing\Modules\Region;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Core\DBConstants\Region\ThreadStatus;
use Symfony\Contracts\Translation\TranslatorInterface;

class ForumGateway extends BaseGateway
{
    private ForumFollowerGateway $forumFollowerGateway;
    protected TranslatorInterface $translator;

    public function __construct(
        Database $db,
        ForumFollowerGateway $forumFollowerGateway,
        TranslatorInterface $translator
    ) {
        parent::__construct($db);
        $this->forumFollowerGateway = $forumFollowerGateway;
        $this->translator = $translator;
    }

    // Thread-related

    public function listThreads(int $regionId, int $subforumId = 0, int $limit = 15, int $offset = 0): array
    {
        $threads = $this->db->fetchAll('
			SELECT 		t.id,
						t.name as title,
						t.`time`,
						UNIX_TIMESTAMP(t.`time`) AS time_ts,
						fs.id AS foodsaver_id,
						IFNULL(fs.name,"abgemeldeter Benutzer") AS foodsaver_name,
						fs.photo AS foodsaver_photo,
						fs.sleep_status,
						p.body AS post_body,
						p.`time` AS post_time,
						UNIX_TIMESTAMP(p.`time`) AS post_time_ts,
						t.last_post_id,
						t.sticky,
						bt.bezirk_id AS regionId,
						bt.bot_theme AS regionSubId,
						creator.id as creator_id,
						creator.name as creator_name,
						creator.photo as creator_photo,
						creator.sleep_status as creator_sleep_status,
						t.status

			FROM 		fs_theme t
						INNER JOIN
						fs_bezirk_has_theme bt
						ON bt.theme_id = t.id
						LEFT JOIN
						fs_theme_post p
						ON p.id = t.last_post_id
						INNER JOIN
						fs_foodsaver fs
						ON  fs.id = p.foodsaver_id
						INNER JOIN
						fs_foodsaver creator
						ON creator.id = t.foodsaver_id

			WHERE       bt.bezirk_id = :regionId
			AND 		bt.bot_theme = :subforumId
			AND 		t.`active` = 1

			ORDER BY t.sticky DESC, t.last_post_id DESC

			LIMIT :limit
			OFFSET :offset
		', [
            ':regionId' => $regionId,
            ':subforumId' => $subforumId,
            ':limit' => $limit,
            ':offset' => $offset,
        ]);

        return $threads ?: [];
    }

    public function getThreadInfo(int $threadId): array
    {
        return $this->db->fetch('
		SELECT		t.name as title,
					bt.bezirk_id as region_id,
					bt.bot_theme as ambassador_forum
		FROM		fs_theme t
		LEFT JOIN   fs_bezirk_has_theme bt ON bt.theme_id = t.id
		WHERE		t.id = :thread_id
		', ['thread_id' => $threadId]);
    }

    public function getThread(int $threadId): array
    {
        return $this->db->fetch('
			SELECT 		t.id,
						b.bezirk_id AS regionId,
						b.bot_theme AS regionSubId,
						t.name as title,
						t.`time`,
						UNIX_TIMESTAMP(t.`time`) AS time_ts,
						t.last_post_id,
						t.`active`,
						t.`sticky`,
						t.foodsaver_id as creator_id,
						t.status

			FROM 		fs_theme t

			LEFT JOIN fs_bezirk_has_theme AS b ON b.theme_id = t.id

			WHERE 		t.id = :thread_id

			LIMIT 1

		', ['thread_id' => $threadId]);
    }

    public function addThread($foodsaverId, $regionId, $title, $body, $isActive, $ambassadorForum = false)
    {
        $isAmbassadorForum = $ambassadorForum ? 1 : 0;
        $threadId = $this->db->insert('fs_theme', [
            'foodsaver_id' => $foodsaverId,
            'name' => $title,
            'time' => date('Y-m-d H:i:s'),
            'active' => $isActive,
            'status' => ThreadStatus::OPEN,
        ]);

        $this->forumFollowerGateway->followThreadByBell($foodsaverId, $threadId);

        $this->db->insert('fs_bezirk_has_theme', [
            'bezirk_id' => $regionId,
            'theme_id' => $threadId,
            'bot_theme' => $isAmbassadorForum
        ]);

        $this->addPost($foodsaverId, $threadId, $body);

        return $threadId;
    }

    public function activateThread(int $threadId): void
    {
        $this->db->update('fs_theme', ['active' => 1], ['id' => $threadId]);
    }

    public function deleteThread($thread_id)
    {
        $this->db->delete('fs_theme_post', ['theme_id' => $thread_id]);
        $this->db->delete('fs_theme', ['id' => $thread_id]);
    }

    public function getBotThreadStatus($thread_id)
    {
        return $this->db->fetch('
			SELECT  ht.bot_theme,
					ht.bezirk_id
			FROM
					fs_bezirk_has_theme ht
			WHERE   ht.theme_id = :theme_id
		', ['theme_id' => $thread_id]);
    }

    public function stickThread($thread_id)
    {
        return $this->db->update(
            'fs_theme',
            ['sticky' => 1],
            ['id' => $thread_id]
        );
    }

    public function unstickThread($thread_id)
    {
        $this->db->update(
            'fs_theme',
            ['sticky' => 0],
            ['id' => $thread_id]
        );
    }

    /**
     * Returns the {@see ThreadStatus} of a thread. Throws an exception if the thread does not exist.
     */
    public function getThreadStatus(int $threadId): int
    {
        return $this->db->fetchValueByCriteria('fs_theme', 'status', ['id' => $threadId]);
    }

    /**
     * Sets the status of a thread and returns whether the status was set successfully, see {@see ThreadStatus}.
     */
    public function setThreadStatus(int $threadId, int $status): bool
    {
        return $this->db->update('fs_theme', ['status' => $status], ['id' => $threadId]) > 0;
    }

    // Post-related

    public function addPost($fs_id, $thread_id, $body)
    {
        $post_id = $this->db->insert(
            'fs_theme_post',
            [
                'theme_id' => $thread_id,
                'foodsaver_id' => $fs_id,
                'body' => $body,
                'time' => date('Y-m-d H:i:s')
            ]
        );

        $this->db->update('fs_theme', ['last_post_id' => $post_id], ['id' => $thread_id]);

        return $post_id;
    }

    private function getPostSelect()
    {
        return '
			SELECT 		fs.id AS author_id,
						IF(fs.deleted_at IS NOT NULL,"' . $this->translator->trans('forum.deleted_user') . '", fs.name) AS author_name,
						fs.photo AS author_photo,
						fs.sleep_status AS author_sleep_status,
						fs.sleep_from AS author_sleep_from,
						fs.sleep_until AS author_sleep_until,
						p.body AS body,
						p.`time`,
						p.id,
						UNIX_TIMESTAMP(p.`time`) AS time_ts,
						b.`type` AS region_type

			FROM 		fs_theme_post p
			INNER JOIN   fs_foodsaver fs
				ON 		p.foodsaver_id = fs.id
			LEFT JOIN   fs_bezirk_has_theme ht
				ON 		ht.theme_id = p.theme_id
			LEFT JOIN	fs_bezirk b
				ON		b.id = ht.bezirk_id';
    }

    /**
     * This method is private because we currently trust the given postIds to exist as well as be not-harmful.
     */
    private function getReactionsForPosts(array $postIds)
    {
        if (empty($postIds)) {
            return [];
        }
        $postIdClause = implode(',', $postIds);
        $reactions = $this->db->fetchAll('
			SELECT
			r.post_id,
			r.`key`,
			r.time,
			r.foodsaver_id,
			fs.name as foodsaver_name

			FROM
			fs_post_reaction r
			LEFT JOIN
			fs_foodsaver fs
			ON
			fs.id = r.foodsaver_id
			WHERE r.post_id IN (' . $postIdClause . ')'
        );
        $out = [];
        foreach ($postIds as $id) {
            $out[$id] = [];
        }
        foreach ($reactions as $r) {
            $user = [
                'id' => $r['foodsaver_id'],
                'name' => $r['foodsaver_name']
            ];
            if (!isset($out[$r['post_id']][$r['key']])) {
                $out[$r['post_id']][$r['key']] = [$user];
            } else {
                $out[$r['post_id']][$r['key']][] = $user;
            }
        }

        return $out;
    }

    public function addReaction($postId, $fsId, $key): bool
    {
        $this->db->insert(
            'fs_post_reaction',
            [
                'post_id' => $postId,
                'foodsaver_id' => $fsId,
                'key' => $key,
                'time' => $this->db->now()
            ]
        );

        return true;
    }

    public function removeReaction($postId, $fsId, $key)
    {
        $this->db->delete(
            'fs_post_reaction',
            [
                'post_id' => $postId,
                'foodsaver_id' => $fsId,
                'key' => $key
            ]
        );
    }

    public function listPosts($threadId)
    {
        $posts = $this->db->fetchAll(
            $this->getPostSelect() . '
			WHERE 		p.theme_id = :threadId

			ORDER BY 	p.`time`
		', ['threadId' => $threadId]);

        if (empty($posts)) {
            return [];
        }

        $postIds = array_column($posts, 'id');
        $reactions = $this->getReactionsForPosts($postIds);
        $mergeReactions = function ($post) use ($reactions) {
            $post['reactions'] = $reactions[$post['id']];

            return $post;
        };

        return array_map($mergeReactions, $posts);
    }

    public function getPost($postId)
    {
        return $this->db->fetch(
            $this->getPostSelect() . '
			WHERE 		p.id = :postId

			ORDER BY 	p.`time`
		', ['postId' => $postId]);
    }

    public function deletePost($id)
    {
        $thread_id = $this->db->fetchValue('SELECT `theme_id` FROM `fs_theme_post` WHERE `id` = :id', ['id' => $id]);
        $this->db->delete('fs_theme_post', ['id' => $id]);

        if ($last_post_id = $this->db->fetchValue(
            'SELECT MAX(`id`) FROM `fs_theme_post` WHERE `theme_id` = :theme_id',
            ['theme_id' => $thread_id]
        )) {
            $this->db->update('fs_theme', ['last_post_id' => $last_post_id], ['id' => $thread_id]);
        } else {
            $this->db->delete('fs_theme', ['id' => $thread_id]);
        }

        return true;
    }

    public function getRegionForPost($post_id)
    {
        return $this->db->fetchValue('
			SELECT 	bt.bezirk_id

			FROM 	fs_bezirk_has_theme bt,
					fs_theme_post tp,
					fs_theme t
			WHERE 	t.id = tp.theme_id
			AND 	t.id = bt.theme_id
			AND 	tp.id = :id
		', ['id' => $post_id]);
    }

    public function getForumsForThread($threadId)
    {
        return $this->db->fetchAll('
		SELECT
			bt.bezirk_id AS forumId,
			bt.bot_theme AS forumSubId
		FROM
			fs_bezirk_has_theme bt

		WHERE bt.theme_id = :threadId
		', ['threadId' => $threadId]);
    }

    public function getThreadForPost(int $postId): ?int
    {
        $threadId = $this->db->fetchByCriteria('fs_theme_post',
            ['theme_id'],
            ['id' => $postId]
        );
        if (empty($threadId)) {
            return null;
        } else {
            return $threadId['theme_id'];
        }
    }
}
