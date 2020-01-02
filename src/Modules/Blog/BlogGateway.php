<?php

namespace Foodsharing\Modules\Blog;

use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Services\SanitizerService;

final class BlogGateway extends BaseGateway
{
	private $bellGateway;
	private $foodsaverGateway;
	private $sanitizerService;

	public function __construct(
		BellGateway $bellGateway,
		Database $db,
		FoodsaverGateway $foodsaverGateway,
		SanitizerService $sanitizerService
	) {
		parent::__construct($db);
		$this->bellGateway = $bellGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->sanitizerService = $sanitizerService;
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
			$val = $this->db->fetchByCriteria('blog_entry', ['bezirk_id', 'foodsaver_id'], ['id' => $article_id]);
		} catch (\Exception $e) {
			// has to be caught until we can check whether a to be fetched value does really exist.
		}

		return $val;
	}

	public function getPost(int $id): array
	{
		return $this->db->fetch('
			SELECT
				b.`id`,
				b.`name`,
				b.`time`,
				UNIX_TIMESTAMP(b.`time`) AS time_ts,
				b.`body`,
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
			AND
				b.id = :fs_id',
		[':fs_id' => $id]);
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

	public function listArticle(array $regionIds, bool $isOrga): array
	{
		$not = '';
		if (!$isOrga) {
			$not = 'WHERE 		`bezirk_id` IN (' . implode(',', array_map('intval', $regionIds)) . ')';
		}

		return $this->db->fetchAll('
			SELECT 	 	`id`,
						`name`,
						`time`,
						UNIX_TIMESTAMP(`time`) AS time_ts,
						`active`,
						`bezirk_id`		
			FROM 		`fs_blog_entry`	
			' . $not . '	
			ORDER BY `id` DESC');
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

	public function add_blog_entry(array $data, string $userName): int
	{
		$id = $this->db->insert(
			'fs_blog_entry',
			[
				'bezirk_id' => (int)$data['bezirk_id'],
				'foodsaver_id' => (int)$data['foodsaver_id'],
				'name' => strip_tags($data['name']),
				'teaser' => strip_tags($data['teaser']),
				'body' => $data['body'],
				'time' => strip_tags($data['time']),
				'picture' => strip_tags($data['picture']),
				'active' => 0
			]
		);

		$recipients = [];
		$orgaTeam = $this->foodsaverGateway->getOrgateam();
		$ambassadorsOfRegion = $this->foodsaverGateway->getAmbassadors($data['bezirk_id']);

		foreach ($orgaTeam as $o) {
			$recipients[$o['id']] = $o;
		}
		foreach ($ambassadorsOfRegion as $b) {
			$recipients[$b['id']] = $b;
		}

		$this->bellGateway->addBell(
			$recipients,
			'blog_new_check_title',
			'blog_new_check',
			'fas fa-bullhorn',
			['href' => '/?page=blog&sub=edit&id=' . $id],
			[
				'user' => $userName,
				'teaser' => $this->sanitizerService->tt($data['teaser'], 100),
				'title' => $data['name']
			],
			'blog-check-' . $id
		);

		return $id;
	}
}
