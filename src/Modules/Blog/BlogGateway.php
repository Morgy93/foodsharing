<?php

namespace Foodsharing\Modules\Blog;

use Carbon\Carbon;
use DateTimeZone;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Blog\DTO\BlogPost;
use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Core\DBConstants\Bell\BellType;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Utility\Sanitizer;

final class BlogGateway extends BaseGateway
{
    private BellGateway $bellGateway;
    private FoodsaverGateway $foodsaverGateway;
    private Sanitizer $sanitizerService;
    private Session $session;

    public function __construct(
        BellGateway $bellGateway,
        Database $db,
        FoodsaverGateway $foodsaverGateway,
        Sanitizer $sanitizerService,
        Session $session
    ) {
        parent::__construct($db);
        $this->bellGateway = $bellGateway;
        $this->foodsaverGateway = $foodsaverGateway;
        $this->sanitizerService = $sanitizerService;
        $this->session = $session;
    }

    public function setPublished(int $blogId, bool $isPublished): int
    {
        return $this->db->update('fs_blog_entry', ['active' => intval($isPublished)], ['id' => $blogId]);
    }

    public function update_blog_entry(int $id, array $data): int
    {
        $data_stripped = [
            'bezirk_id' => $data['bezirk_id'],
            'foodsaver_id' => $data['foodsaver_id'],
            'name' => strip_tags($data['name']),
            'teaser' => strip_tags($data['teaser']),
            'body' => $data['body'],
            'time' => strip_tags($data['time']),
        ];

        if (!empty($data['picture'])) {
            $data_stripped['picture'] = strip_tags($data['picture']);
        }

        return $this->db->update(
            'fs_blog_entry',
            $data_stripped,
            ['id' => $id]
        );
    }

    public function getAuthorOfPost(int $article_id)
    {
        $val = false;
        try {
            $val = $this->db->fetchByCriteria('fs_blog_entry', ['bezirk_id', 'foodsaver_id'], ['id' => $article_id]);
        } catch (\Exception $e) {
            // has to be caught until we can check whether a to be fetched value does really exist.
        }

        return $val;
    }

    /**
     * Returns the blog post with the given id, or null if the id does not exist.
     */
    public function getPost(int $id): ?BlogPost
    {
        $blogPost = $this->db->fetch('
			SELECT
				b.`id`,
				b.`name`,
				b.`time`,
				UNIX_TIMESTAMP(b.`time`) AS time_ts,
				b.`body`,
				b.`picture`,
				CONCAT(fs.name," ",fs.nachname) AS fs_name
			FROM
				`fs_blog_entry` b,
				`fs_foodsaver` fs
			WHERE
				b.foodsaver_id = fs.id
			AND
				b.`active` = 1
			AND
				b.id = :fs_id',
            [':fs_id' => $id]);

        if (empty($blogPost)) {
            return null;
        }

        return BlogPost::create(
            $blogPost['id'],
            $blogPost['name'],
            $blogPost['body'],
            Carbon::createFromTimestamp($blogPost['time_ts'], new DateTimeZone('Europe/Berlin')),
            $blogPost['fs_name'],
            $blogPost['picture']
        );
    }

    public function listNews(int $page): array
    {
        $page = ($page - 1) * 10;

        return $this->db->fetchAll(
            '
			SELECT
				b.`id`,
				b.`name`,
				b.`time`,
				UNIX_TIMESTAMP(b.`time`) AS time_ts,
				b.`active`,
				b.`teaser`,
				b.`time`,
				b.`picture`,
				CONCAT(fs.name," ",fs.nachname) AS fs_name
			FROM
				`fs_blog_entry` b,
				`fs_foodsaver` fs
			WHERE
				b.foodsaver_id = fs.id
			AND
				b.`active` = 1
			ORDER BY
				b.`id` DESC
			LIMIT :page,10',
            [':page' => $page]
        );
    }

    public function getBlogpostList(): array
    {
        if ($this->session->mayRole(Role::ORGA)) {
            $filter = '';
        } else {
            $ownRegionIds = implode(',', array_map('intval', $this->session->listRegionIDs()));
            $filter = 'WHERE `bezirk_id` IN (' . $ownRegionIds . ')';
        }

        return $this->db->fetchAll('
			SELECT 	 	`id`,
						`name`,
						`foodsaver_id`,
						`time`,
						UNIX_TIMESTAMP(`time`) AS time_ts,
						`active`,
						`teaser`,
						`bezirk_id`
			FROM 		`fs_blog_entry`
			' . $filter . '
			ORDER BY `time` DESC');
    }

    public function del_blog_entry(int $id): int
    {
        return $this->db->delete('fs_blog_entry', ['id' => $id]);
    }

    public function getOne_blog_entry(int $id): array
    {
        return $this->db->fetch(
            '
			SELECT
			`id`,
			`bezirk_id`,
			`foodsaver_id`,
			`active`,
			`name`,
			`teaser`,
			`body`,
			`time`,
			UNIX_TIMESTAMP(`time`) AS time_ts,
			`picture`
			FROM 		`fs_blog_entry`
			WHERE 		`id` = :fs_id',
            [':fs_id' => $id]
        );
    }

    public function add_blog_entry(array $data): int
    {
        $regionId = intval($data['bezirk_id']);
        $active = intval($this->session->mayRole(Role::ORGA) || $this->session->isAdminFor($regionId));

        $id = $this->db->insert(
            'fs_blog_entry',
            [
                'bezirk_id' => $regionId,
                'foodsaver_id' => (int)$data['foodsaver_id'],
                'name' => strip_tags($data['name']),
                'teaser' => strip_tags($data['teaser']),
                'body' => $data['body'],
                'time' => strip_tags($data['time']),
                'picture' => strip_tags($data['picture']),
                'active' => $active,
            ]
        );

        $foodsaver = [];
        $orgateam = $this->foodsaverGateway->getOrgaTeam();
        $botschafter = $this->foodsaverGateway->getAdminsOrAmbassadors($regionId);

        foreach ($orgateam as $o) {
            $foodsaver[$o['id']] = $o;
        }
        foreach ($botschafter as $b) {
            $foodsaver[$b['id']] = $b;
        }

        $bellData = Bell::create(
            'blog_new_check_title',
            'blog_new_check',
            'fas fa-bullhorn',
            ['href' => '/?page=blog&sub=edit&id=' . $id],
            [
                'user' => $this->session->user('name'),
                'teaser' => $this->sanitizerService->tt($data['teaser'], 100),
                'title' => $data['name']
            ],
            BellType::createIdentifier(BellType::NEW_BLOG_POST, $id)
        );
        $this->bellGateway->addBell($foodsaver, $bellData);

        return $id;
    }
}
