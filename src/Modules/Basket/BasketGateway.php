<?php

namespace Foodsharing\Modules\Basket;

use Foodsharing\Modules\Core\BaseGateway;

class BasketGateway extends BaseGateway
{
	public function getBasketCoordinates(): array
	{
		$stm = '
			SELECT id,lat,lon 
			FROM fs_basket 
			WHERE status = 1
			';

		return $this->db->fetchAll($stm);
	}

	public function addBasket($desc, $pic, $tel, $contact_type, $weight, $location_type, $lat, $lon, $bezirk_id, $fsId): int
	{
		$appost = 1;

		if (isset($_REQUEST['appost']) && '0' === $_REQUEST['appost']) {
			$appost = 0;
		}

		return $this->db->insert(
			'fs_basket',
			[
				'foodsaver_id' => $fsId,
				'status' => 1,
				'time' => date('Y-m-d H:i:s'),
				'description' => strip_tags($desc),
				'picture' => strip_tags($pic),
				'tel' => strip_tags($tel['tel']),
				'handy' => strip_tags($tel['handy']),
				'contact_type' => strip_tags($contact_type),
				'location_type' => (int)$location_type,
				'weight' => (float)$weight,
				'lat' => (float)$lat,
				'lon' => (float)$lon,
				'bezirk_id' => (int)$bezirk_id,
				'appost' => $appost,
				'until' => date('Y-m-d', time() + 1209600),
			]
		);
	}

	public function getBasket($id, $status = false)
	{
		$status_sql = '';

		if ($status !== false) {
			$status_sql = 'AND `status` = ' . (int)$status;
		}

		$stm = '
			SELECT
				b.id,
				b.status,
				b.description,
				b.picture,
				b.contact_type,
				b.tel,
				b.handy,
				b.fs_id AS fsf_id,
				b.lat,
				b.lon,
				b.foodsaver_id,
				UNIX_TIMESTAMP(b.time) AS time_ts,
				UNIX_TIMESTAMP(b.until) AS until_ts,
				fs.id AS fs_id,
				fs.name AS fs_name,
				fs.photo AS fs_photo,
				fs.sleep_status
				
			FROM
				fs_basket b,
				fs_foodsaver fs
				
			WHERE 
				b.foodsaver_id = fs.id
			
			AND
				b.id = :id
			' . $status_sql . '				
		';
		$basket = $this->db->fetch($stm, [':id' => (int)$id]);

		$stm = '
				SELECT 
					fs.name AS fs_name,
					fs.photo AS fs_photo,
					fs.id AS fs_id
					
				FROM
					fs_foodsaver fs
					
				WHERE
					fs.id = ' . (int)$basket['foodsaver_id'] . '
					
			';
		if ('0' === $basket['fsf_id'] && $fs = $this->db->fetch(
				$stm,
				['foodsaver_id' => (int)$basket['foodsaver_id']]
			)) {
			$basket = array_merge($basket, $fs);
		}

		return $basket;
	}

	public function addTypes($basket_id, $types): void
	{
		if (!empty($types)) {
			foreach ($types as $t) {
				$this->db->insert('fs_basekt_has_types', ['basket_id' => $basket_id, 'types_id' => (int)$t]);
			}
		}
	}

	public function listRequests($basket_id, $id): array
	{
		$stm = '		
				SELECT
					UNIX_TIMESTAMP(a.time) AS time_ts,
					fs.name AS fs_name,
					fs.photo AS fs_photo,
					fs.id AS fs_id,
					fs.geschlecht AS fs_gender,
					fs.sleep_status,
					b.id		
		
				FROM
					fs_basket_anfrage a,
					fs_basket b,
					fs_foodsaver fs
		
				WHERE
					a.basket_id = b.id
		
				AND
					a.`status` IN(0,1)
		
				AND
					a.foodsaver_id = fs.id
		
				AND
					b.foodsaver_id = :foodsaver_id
		
				AND
					a.basket_id = :basket_id		
				';

		return $this->db->fetchAll($stm, [':foodsaver_id' => $id, ':basket_id' => $basket_id]);
	}

	public function getRequest($basket_id, $fs_id, $id)
	{
		$stm = '		
				SELECT
					UNIX_TIMESTAMP(a.time) AS time_ts,
					fs.name AS fs_name,
					fs.photo AS fs_photo,
					fs.id AS fs_id,
					fs.geschlecht AS fs_gender,
					b.id		
		
				FROM
					fs_basket_anfrage a,
					fs_basket b,
					fs_foodsaver fs
		
				WHERE
					a.basket_id = b.id
		
				AND
					a.`status` IN(0,1)
		
				AND
					a.foodsaver_id = fs.id
		
				AND
					b.foodsaver_id = :foodsaver_id
				
				AND
					a.foodsaver_id = :fs_id
				
				AND
					a.basket_id = :basket_id		
				';

		return $this->db->fetch($stm, [':foodsaver_id' => $id, ':fs_id' => $fs_id, ':basket_id' => $basket_id]);
	}

	public function listUpdates($fsId): array
	{
		$stm = '
			SELECT 
				UNIX_TIMESTAMP(a.time) AS time_ts,
				fs.name AS fs_name,
				fs.photo AS fs_photo,
				fs.id AS fs_id,
				fs.sleep_status,
				b.id,
				b.description				
				
			FROM 
				fs_basket_anfrage a, 
				fs_basket b,
				fs_foodsaver fs
				
			WHERE 
				a.basket_id = b.id 
				
			AND 
				a.`status` IN(0,1)
				
			AND
				a.foodsaver_id = fs.id
				
			AND
				b.foodsaver_id = :foodsaver_id
				
			ORDER BY
				a.`time` DESC				
		';

		return $this->db->fetchAll($stm, [':foodsaver_id' => $fsId]);
	}

	public function getUpdateCount($id): int
	{
		$stm = '
				SELECT COUNT(a.basket_id)
				FROM fs_basket_anfrage a, fs_basket b
				WHERE a.basket_id = b.id
				AND a.`status` = 0
				AND b.foodsaver_id = :foodsaver_id
			';

		return (int)$this->db->fetchValue($stm, [':foodsaver_id' => $id]);
	}

	public function addArt($basket_id, $types): void
	{
		if (!empty($types)) {
			foreach ($types as $t) {
				$this->db->insert('fs_basket_has_art', ['basket_id' => $basket_id, 'art_id' => (int)$t]);
			}
		}
	}

	public function removeBasket($id, $fsId): int
	{
		return $this->db->update('fs_basket', ['status' => 3], ['id' => (int)$id, 'foodsaver_id' => $fsId]);
	}

	public function listMyBaskets($fsId)
	{
		$stm = '
			SELECT 
				`id`,
				`description`,
				`picture`,
				UNIX_TIMESTAMP(`time`) AS time_ts
				
			FROM 
				fs_basket
				
			WHERE
				`foodsaver_id` = :foodsaver_id
				
			AND 
				`status` = 1				
		';
		if ($baskets = $this->db->fetchAll($stm, [':foodsaver_id' => $fsId])
		) {
			foreach ($baskets as $key => $b) {
				$stm = 'SELECT COUNT(foodsaver_id) FROM fs_basket_anfrage WHERE basket_id = :basket_id AND status < 10';
				$baskets[$key]['req_count'] = $this->db->fetchValue($stm, [':basket_id' => (int)$b['id']]);
			}

			return $baskets;
		}

		return false;
	}

	public function follow($basket_id, $fsId): void
	{
		$stm = 'SELECT 1 FROM `fs_basket_anfrage` WHERE basket_id = :basket_id AND foodsaver_id = :foodsaver_id AND status <= 9';
		$status = $this->db->fetchValue($stm, [':basket_id' => (int)$basket_id, ':foodsaver_id' => $fsId]);

		if (!$status) {
			$this->db->insert(
				'fs_basket_anfrage',
				[
					'foodsaver_id' => (int)$fsId,
					'basket_id' => (int)$basket_id,
					'status' => 9,
					'time' => NOW(),
					'appost' => 0,
				]
			);
		}
	}

	public function setStatus($basket_id, $status, $fsid): void
	{
		$appost = 1;
		if (isset($_REQUEST['appost']) && '0' === $_REQUEST['appost']) {
			$appost = 0;
		}

		$this->db->insert(
			'fs_basket_anfrage',
			[
				'foodsaver_id' => (int)$fsid,
				'basket_id' => (int)$basket_id,
				'status' => (int)$status,
				'time' => date('Y-m-d H:i:s'),
				'appost' => $appost,
			]
		);
	}
}
