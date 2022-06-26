<?php

namespace Foodsharing\Modules\PassportGenerator;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Region\RegionGateway;

final class PassportGeneratorGateway extends BaseGateway
{
	private RegionGateway $regionGateway;

	public function __construct(Database $db, RegionGateway $regionGateway)
	{
		$this->regionGateway = $regionGateway;

		parent::__construct($db);
	}

	public function passGen(int $bot_id, int $fsid): int
	{
		return $this->db->insert('fs_pass_gen', [
			'foodsaver_id' => $fsid,
			'date' => $this->db->now(),
			'bot_id' => $bot_id,
		]);
	}

	public function updateLastGen(array $foodsaver): int
	{
		return $this->db->update('fs_foodsaver', ['last_pass' => $this->db->now()], ['id' => $foodsaver]);
	}

	public function getPassFoodsaver(int $regionId): array
	{
		$stm = '
				SELECT 	fs.`id`,
						CONCAT(fs.`name`," ",fs.`nachname`) AS `name`,
				       	fs.geschlecht as gender_id,
						fs.verified as is_verified,
						fs.last_pass as last_pass_datetime,
						fs.photo,
				       	fs.rolle as role_id,
						fs.last_login as last_login_datetime,

						b.name AS bezirk_name,
						b.id AS bezirk_id

				FROM 	fs_foodsaver_has_bezirk fb,
						fs_foodsaver fs,
						fs_bezirk b

				WHERE 	fb.foodsaver_id = fs.id
				AND 	fb.bezirk_id = b.id
				AND 	fb.`bezirk_id` IN(' . implode(',', $this->regionGateway->listIdsForDescendantsAndSelf($regionId, true, false)) . ')
				AND		fs.deleted_at IS NULL

				ORDER BY bezirk_name, fs.name
		';
		$req = $this->db->fetchAll($stm);

		$out = [];
		foreach ($req as $r) {
			if (!isset($out[$r['bezirk_id']])) {
				$out[$r['bezirk_id']] = [
					'id' => $r['bezirk_id'],
					'bezirk' => $r['bezirk_name'],
					'foodsaver' => []
				];
			}
			$out[$r['bezirk_id']]['foodsaver'][] = $r;
		}

		return $out;
	}
}
