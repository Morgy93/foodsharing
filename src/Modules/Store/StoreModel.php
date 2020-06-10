<?php

namespace Foodsharing\Modules\Store;

use Foodsharing\Lib\Db\Db;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Message\MessageGateway;
use Foodsharing\Modules\Region\RegionGateway;

class StoreModel extends Db
{
	private $bellGateway;
	private $storeGateway;
	private $regionGateway;
	private $messageGateway;

	public function __construct(
		BellGateway $bellGateway,
		StoreGateway $storeGateway,
		RegionGateway $regionGateway,
		MessageGateway $messageGateway
	) {
		$this->bellGateway = $bellGateway;
		$this->storeGateway = $storeGateway;
		$this->regionGateway = $regionGateway;
		$this->messageGateway = $messageGateway;

		parent::__construct();
	}

	public function signout($storeId, $fsId)
	{
		$storeId = (int)$storeId;
		$fsId = (int)$fsId;
		$this->del('DELETE FROM `fs_betrieb_team` WHERE `betrieb_id` = ' . $storeId . ' AND `foodsaver_id` = ' . $fsId . ' ');
		$this->del('DELETE FROM `fs_abholer` WHERE `betrieb_id` = ' . $storeId . ' AND `foodsaver_id` = ' . $fsId . ' AND `date` > NOW()');

		if ($tcid = $this->storeGateway->getBetriebConversation($storeId)) {
			$this->messageGateway->deleteUserFromConversation($tcid, $fsId);
		}
		if ($scid = $this->storeGateway->getBetriebConversation($storeId, true)) {
			$this->messageGateway->deleteUserFromConversation($scid, $fsId);
		}
	}

	public function getOne_betrieb($storeId)
	{
		$out = $this->qRow('
			SELECT
			`id`,
			`betrieb_status_id`,
			`bezirk_id`,
			`plz`,
			`stadt`,
			`lat`,
			`lon`,
			`kette_id`,
			`betrieb_kategorie_id`,
			`name`,
			`str`,
			`hsnr`,
			`status_date`,
			`status`,
			`ansprechpartner`,
			`telefon`,
			`fax`,
			`email`,
			`begin`,
			`besonderheiten`,
			`ueberzeugungsarbeit`,
			`presse`,
			`sticker`,
			`abholmenge`,
			`prefetchtime`,
			`public_info`,
			`public_time`

			FROM 		`fs_betrieb`

			WHERE 		`id` = ' . (int)$storeId);

		$out['lebensmittel'] = $this->qCol('
				SELECT 		`lebensmittel_id`

				FROM 		`fs_betrieb_has_lebensmittel`
				WHERE 		`betrieb_id` = ' . (int)$storeId . '
			');
		$out['foodsaver'] = $this->qCol('
				SELECT 		`foodsaver_id`

				FROM 		`fs_betrieb_team`
				WHERE 		`betrieb_id` = ' . (int)$storeId . '
				AND 		`active` = 1
			');

		return $out;
	}

	public function listBetriebReq($bezirk_id)
	{
		return $this->q('
				SELECT 	fs_betrieb.id,
						`fs_betrieb`.betrieb_status_id,
						fs_betrieb.plz,
						fs_betrieb.added,
						`stadt`,
						fs_betrieb.kette_id,
						fs_betrieb.betrieb_kategorie_id,
						fs_betrieb.name,
						CONCAT(fs_betrieb.str," ",fs_betrieb.hsnr) AS anschrift,
						fs_betrieb.str,
						fs_betrieb.hsnr,
						CONCAT(fs_betrieb.lat,", ",fs_betrieb.lon) AS geo,
						fs_betrieb.`betrieb_status_id`,
						fs_bezirk.name AS bezirk_name

				FROM 	fs_betrieb,
						fs_bezirk

				WHERE 	fs_betrieb.bezirk_id = fs_bezirk.id
				AND 	fs_betrieb.bezirk_id IN(' . implode(',', $this->regionGateway->listIdsForDescendantsAndSelf($bezirk_id)) . ')

		');
	}

	public function update_betrieb($id, $data)
	{
		if (isset($data['lebensmittel']) && is_array($data['lebensmittel'])) {
			$this->del('
					DELETE FROM 	`fs_betrieb_has_lebensmittel`
					WHERE 			`betrieb_id` = ' . (int)$id . '
				');

			foreach ($data['lebensmittel'] as $lebensmittel_id) {
				$this->insert('
						INSERT INTO `fs_betrieb_has_lebensmittel`
						(
							`betrieb_id`,
							`lebensmittel_id`
						)
						VALUES
						(
							' . (int)$id . ',
							' . (int)$lebensmittel_id . '
						)
					');
			}
		}

		if (!isset($data['status_date'])) {
			$data['status_date'] = date('Y-m-d H:i:s');
		}

		$name = $data['name'];

		return $this->update('
		UPDATE 	`fs_betrieb`

		SET 	`betrieb_status_id` =  ' . (int)$data['betrieb_status_id'] . ',
				`bezirk_id` =  ' . (int)$data['bezirk_id'] . ',
				`plz` =  ' . $this->strval($data['plz']) . ',
				`stadt` =  ' . $this->strval($data['stadt']) . ',
				`lat` =  ' . $this->strval($data['lat']) . ',
				`lon` =  ' . $this->strval($data['lon']) . ',
				`kette_id` =  ' . (int)$data['kette_id'] . ',
				`betrieb_kategorie_id` =  ' . (int)$data['betrieb_kategorie_id'] . ',
				`name` =  ' . $this->strval($name) . ',
				`str` =  ' . $this->strval($data['str']) . ',
				`hsnr` =  ' . $this->strval($data['hsnr']) . ',
				`status_date` =  ' . $this->dateval($data['status_date']) . ',
				`ansprechpartner` =  ' . $this->strval($data['ansprechpartner']) . ',
				`telefon` =  ' . $this->strval($data['telefon']) . ',
				`fax` =  ' . $this->strval($data['fax']) . ',
				`email` =  ' . $this->strval($data['email']) . ',
				`begin` =  ' . $this->dateval($data['begin']) . ',
				`besonderheiten` =  ' . $this->strval($data['besonderheiten']) . ',
				`public_info` =  ' . $this->strval($data['public_info']) . ',
				`public_time` =  ' . (int)$data['public_time'] . ',
				`ueberzeugungsarbeit` =  ' . (int)$data['ueberzeugungsarbeit'] . ',
				`presse` =  ' . (int)$data['presse'] . ',
				`sticker` =  ' . (int)$data['sticker'] . ',
				`abholmenge` =  ' . (int)$data['abholmenge'] . ',
				`prefetchtime` = ' . (int)$data['prefetchtime'] . '

		WHERE 	`id` = ' . (int)$id);
	}

	public function add_betrieb($data)
	{
		$id = $this->insert('
			INSERT INTO 	`fs_betrieb`
			(
			`betrieb_status_id`,
			`bezirk_id`,
			`added`,
			`plz`,
			`stadt`,
			`lat`,
			`lon`,
			`kette_id`,
			`betrieb_kategorie_id`,
			`name`,
			`str`,
			`hsnr`,
			`status_date`,
			`status`,
			`ansprechpartner`,
			`telefon`,
			`fax`,
			`email`,
			`begin`,
			`besonderheiten`,
			`public_info`,
			`public_time`,
			`ueberzeugungsarbeit`,
			`presse`,
			`sticker`,
			`abholmenge`,
			`prefetchtime`
			)
			VALUES
			(
			' . (int)$data['betrieb_status_id'] . ',
			' . (int)$data['bezirk_id'] . ',
			NOW(),
			' . $this->strval($data['plz']) . ',
			' . $this->strval($data['stadt']) . ',
			' . $this->strval($data['lat']) . ',
			' . $this->strval($data['lon']) . ',
			' . (int)$data['kette_id'] . ',
			' . (int)$data['betrieb_kategorie_id'] . ',
			' . $this->strval($data['name']) . ',
			' . $this->strval($data['str']) . ',
			' . $this->strval($data['hsnr']) . ',
			' . $this->dateval($data['status_date']) . ',
			' . (int)$data['betrieb_status_id'] . ',
			' . $this->strval($data['ansprechpartner']) . ',
			' . $this->strval($data['telefon']) . ',
			' . $this->strval($data['fax']) . ',
			' . $this->strval($data['email']) . ',
			' . $this->dateval($data['begin']) . ',
			' . $this->strval($data['besonderheiten']) . ',
			' . $this->strval($data['public_info']) . ',
			' . (int)$data['public_time'] . ',
			' . (int)$data['ueberzeugungsarbeit'] . ',
			' . (int)$data['presse'] . ',
			' . (int)$data['sticker'] . ',
			' . (int)$data['abholmenge'] . ',
			' . (int)$data['prefetchtime'] . '
			)');

		if (isset($data['lebensmittel']) && is_array($data['lebensmittel'])) {
			foreach ($data['lebensmittel'] as $lebensmittel_id) {
				$this->insert('
						INSERT INTO `fs_betrieb_has_lebensmittel`
						(
							`betrieb_id`,
							`lebensmittel_id`
						)
						VALUES
						(
							' . (int)$id . ',
							' . (int)$lebensmittel_id . '
						)
					');
			}
		}

		$this->createTeamConversation($id);
		$this->createSpringerConversation($id);

		$this->addBetriebTeam($id, $data['foodsaver'], $data['foodsaver']);

		return $id;
	}

	public function acceptRequest($fsid, $storeId)
	{
		$betrieb = $this->getVal('name', 'betrieb', $storeId);

		$bellData = Bell::create('store_request_accept_title', 'store_request_accept', 'fas fa-user-check', [
			'href' => '/?page=fsbetrieb&id=' . (int)$storeId
		], [
			'user' => $this->session->user('name'),
			'name' => $betrieb
		], 'store-arequest-' . (int)$fsid);
		$this->bellGateway->addBell((int)$fsid, $bellData);

		if ($scid = $this->storeGateway->getBetriebConversation($storeId, true)) {
			$this->messageGateway->deleteUserFromConversation($scid, $fsid);
		}

		if ($tcid = $this->storeGateway->getBetriebConversation($storeId, false)) {
			$this->messageGateway->addUserToConversation($tcid, $fsid);
		}

		return $this->update('
					UPDATE 	 	`fs_betrieb_team`
					SET 		`active` = 1, `stat_add_date` = NOW()
					WHERE 		`betrieb_id` = ' . (int)$storeId . '
					AND 		`foodsaver_id` = ' . (int)$fsid . '
		');
	}

	public function warteRequest($fsid, $storeId)
	{
		$betrieb = $this->getVal('name', 'betrieb', $storeId);

		$bellData = Bell::create('store_request_accept_wait_title', 'store_request_accept_wait', 'fas fa-user-tag', [
			'href' => '/?page=fsbetrieb&id=' . (int)$storeId
		], [
			'user' => $this->session->user('name'),
			'name' => $betrieb
		], 'store-wrequest-' . (int)$fsid);
		$this->bellGateway->addBell((int)$fsid, $bellData);

		if ($scid = $this->storeGateway->getBetriebConversation($storeId, true)) {
			$this->messageGateway->addUserToConversation($scid, $fsid);
		}

		return $this->update('
					UPDATE 	 	`fs_betrieb_team`
					SET 		`active` = 2
					WHERE 		`betrieb_id` = ' . (int)$storeId . '
					AND 		`foodsaver_id` = ' . (int)$fsid . '
		');
	}

	public function denyRequest($fsid, $storeId)
	{
		$betrieb = $this->getVal('name', 'betrieb', $storeId);

		$bellData = Bell::create('store_request_deny_title', 'store_request_deny', 'fas fa-user-times', [
			'href' => '/?page=fsbetrieb&id=' . (int)$storeId
		], [
			'user' => $this->session->user('name'),
			'name' => $betrieb
		], 'store-drequest-' . (int)$fsid);
		$this->bellGateway->addBell((int)$fsid, $bellData);

		return $this->update('
					DELETE FROM 	`fs_betrieb_team`
					WHERE 		`betrieb_id` = ' . (int)$storeId . '
					AND 		`foodsaver_id` = ' . (int)$fsid . '
		');
	}

	public function teamRequest($fsid, $storeId)
	{
		return $this->insert('
			REPLACE INTO `fs_betrieb_team`
			(
				`betrieb_id`,
				`foodsaver_id`,
				`verantwortlich`,
				`active`
			)
			VALUES
			(
				' . (int)$storeId . ',
				' . (int)$fsid . ',
				0,
				0
			)');
	}

	/* creates an empty team conversation for the given store */
	public function createTeamConversation(int $storeId): int
	{
		$teamIds = array_map(function ($fs) { return $fs['id']; }, $this->storeGateway->getStoreTeam($storeId));
		$tcid = $this->messageGateway->createConversation($teamIds, true);
		$betrieb = $this->storeGateway->getMyStore($this->session->id(), $storeId);

		$this->update('
				UPDATE	`fs_betrieb` SET team_conversation_id = ' . (int)$tcid . ' WHERE id = ' . (int)$storeId . '
			');

		return $tcid;
	}

	/* creates an empty springer conversation for the given store */
	public function createSpringerConversation(int $storeId): int
	{
		$standbyTeamMemberIds = array_map(function ($fs) { return $fs['id']; }, $this->storeGateway->getBetriebSpringer($storeId));
		$standbyTeamChatId = $this->messageGateway->createConversation($standbyTeamMemberIds, true);
		$store = $this->storeGateway->getMyStore($this->session->id(), $storeId);
		$this->update('
				UPDATE	`fs_betrieb` SET springer_conversation_id = ' . (int)$standbyTeamChatId . ' WHERE id = ' . (int)$storeId . '
			');

		return $standbyTeamChatId;
	}

	public function addBetriebTeam(int $storeId, array $member, ?array $verantwortlicher = null)
	{
		if (empty($member)) {
			return false;
		}
		if (!$verantwortlicher) {
			$verantwortlicher = [
				$this->session->id() => true
			];
		}

		$tmp = [];
		foreach ($verantwortlicher as $vv) {
			$tmp[$vv] = $vv;
		}
		$verantwortlicher = $tmp;

		$values = [];
		$member_ids = [];

		foreach ($member as $m) {
			$v = 0;
			if (isset($verantwortlicher[$m])) {
				$v = 1;
			}
			$member_ids[] = (int)$m;
			$values[] = '(' . (int)$storeId . ',' . (int)$m . ',' . $v . ',1,NOW())';
		}

		$this->del('DELETE FROM `fs_betrieb_team` WHERE `betrieb_id` = ' . (int)$storeId . ' AND active = 1 AND foodsaver_id NOT IN(' . implode(',', $member_ids) . ')');

		$sql = 'INSERT IGNORE INTO `fs_betrieb_team` (`betrieb_id`,`foodsaver_id`,`verantwortlich`,`active`,`stat_add_date`) VALUES ' . implode(',', $values);

		if ($cid = $this->storeGateway->getBetriebConversation($storeId)) {
			$this->messageGateway->setConversationMembers($cid, $member_ids);
		}

		if ($sid = $this->storeGateway->getBetriebConversation($storeId, true)) {
			foreach ($verantwortlicher as $user) {
				$this->messageGateway->addUserToConversation($sid, $user);
			}
		}

		if ($this->sql($sql)) {
			$this->update('
				UPDATE	`fs_betrieb_team` SET verantwortlich = 0 WHERE betrieb_id = ' . (int)$storeId . '
			');
			$this->update('
				UPDATE	`fs_betrieb_team` SET verantwortlich = 1 WHERE betrieb_id = ' . (int)$storeId . ' AND foodsaver_id IN(' . implode(',', $verantwortlicher) . ')
			');

			return true;
		}

		return false;
	}
}
