<?php

namespace Foodsharing\Modules\Activity;

use Foodsharing\Lib\Db\Db;
use Foodsharing\Modules\Mailbox\MailboxGateway;
use Foodsharing\Services\ImageService;
use Foodsharing\Services\SanitizerService;

class ActivityModel extends Db
{
	private $activityGateway;
	private $sanitizerService;
	private $imageService;
	private $mailboxGateway;

	public function __construct(
		ActivityGateway $activityGateway,
		SanitizerService $sanitizerService,
		ImageService $imageService,
		MailboxGateway $mailboxGateway
	) {
		parent::__construct();
		$this->activityGateway = $activityGateway;
		$this->sanitizerService = $sanitizerService;
		$this->imageService = $imageService;
		$this->mailboxGateway = $mailboxGateway;
	}

	public function loadBasketWallUpdates($page = 0)
	{
		$updates = array();
		if ($up = $this->activityGateway->fetchAllBasketWallUpdates($this->session->id(), $page)) {
			$updates = $up;
		}

		if ($up = $this->activityGateway->fetchAllWallpostsFromFoodBaskets($this->session->id(), $page)) {
			$updates = array_merge($updates, $up);
		}

		if (!empty($updates)) {
			$out = array();

			foreach ($updates as $u) {
				/*
				 * quick fix later list all comments in a package
				*/
				if (isset($hb[$u['basket_id']])) {
					continue;
				}
				$hb[$u['basket_id']] = true;

				$out[] = [
					'type' => 'foodbasket',
					'data' => [
						'fs_id' => $u['fs_id'],
						'fs_name' => $u['fs_name'],
						'basked_id' => $u['basket_id'],
						'desc' => $u['body'],
						'time' => $u['time'],
						'icon' => $this->imageService->img($u['fs_photo'], 50),
						'time_ts' => $u['time_ts'],
						'quickreply' => '/xhrapp.php?app=wallpost&m=quickreply&table=basket&id=' . (int)$u['basket_id']
					]
				];
			}

			return $out;
		}

		return false;
	}

	public function loadFriendWallUpdates($hidden_ids, $page = 0)
	{
		$buddy_ids = array();

		if ($b = $this->session->get('buddy-ids')) {
			$buddy_ids = $b;
		}

		$buddy_ids[$this->session->id()] = $this->session->id();

		$bids = array();
		foreach ($buddy_ids as $id) {
			if (!isset($hidden_ids[$id])) {
				$bids[] = $id;
			}
		}

		if ($updates = $this->activityGateway->fetchAllFriendWallUpdates($bids, $page)) {
			$out = array();
			$hb = array();
			foreach ($updates as $u) {
				/*
				 * quick fix later list all comments in a package
				*/
				if (isset($hb[$u['fs_id']])) {
					continue;
				}
				$hb[$u['fs_id']] = true;

				if (empty($u['gallery'])) {
					$u['gallery'] = '[]';
				}

				$out[] = [
					'type' => 'friendWall',
					'data' => [
						'fs_id' => $u['fs_id'],
						'fs_name' => $u['fs_name'],
						'poster_id' => $u['poster_id'],
						'poster_name' => $u['poster_name'],
						'desc' => $u['body'],
						'time' => $u['time'],
						'icon' => $this->imageService->img($u['fs_photo'], 50),
						'time_ts' => $u['time_ts'],
						'gallery' => $u['gallery']
					]
				];
			}

			return $out;
		}

		return false;
	}

	public function loadMailboxUpdates($page = 0, $hidden_ids = false)
	{
		if ($boxes = $this->mailboxGateway->getBoxes($this->session->isAmbassador(), $this->session->id(), $this->session->may('bieb'))) {
			$mb_ids = array();
			foreach ($boxes as $b) {
				if (!isset($hidden_ids[$b['id']])) {
					$mb_ids[] = $b['id'];
				}
			}

			if (count($mb_ids) === 0) {
				return false;
			}

			if ($updates = $this->activityGateway->fetchAllMailboxUpdates($mb_ids, $page)) {
				$out = array();
				foreach ($updates as $u) {
					$sender = @json_decode($u['sender'], true);

					$from = 'E-Mail';

					if ($sender !== null) {
						if (isset($sender['from']) && !empty($sender['from'])) {
							$from = '<a title="' . $sender['mailbox'] . '@' . $sender['host'] . '" href="/?page=mailbox&mailto=' . urlencode($sender['mailbox'] . '@' . $sender['host']) . '">' . $this->ttt($sender['personal'], 22) . '</a>';
						} elseif (isset($sender['mailbox'])) {
							$from = '<a title="' . $sender['mailbox'] . '@' . $sender['host'] . '" href="/?page=mailbox&mailto=' . urlencode($sender['mailbox'] . '@' . $sender['host']) . '">' . $this->ttt($sender['mailbox'] . '@' . $sender['host'], 22) . '</a>';
						}
					}

					$out[] = [
						'type' => 'mailbox',
						'data' => [
							'sender_email' => $sender['mailbox'] . '@' . $sender['host'],
							'mailbox_id' => $u['id'],
							'subject' => $u['subject'],
							'mailbox_name' => $u['mb_name'] . '@' . PLATFORM_MAILBOX_HOST,
							'desc' => $u['body'],
							'time' => $u['time'],
							'icon' => '/img/mailbox-50x50.png',
							'time_ts' => $u['time_ts'],
							'quickreply' => '/xhrapp.php?app=mailbox&m=quickreply&mid=' . (int)$u['id']
						]
					];
				}

				return $out;
			}
		}

		return false;
	}

	private function ttt($str, $length = 160)
	{
		if (strlen($str) > $length) {
			$str = substr($str, 0, ($length - 4)) . '...';
		}

		return $str;
	}

	public function loadForumUpdates($page = 0, $bids_not_load = false): array
	{
		$myRegionIds = $this->session->listRegionIDs();
		$region_ids = array();
		if ($myRegionIds === [] || count($myRegionIds) === 0) {
			return [];
		}

		foreach ($myRegionIds as $regionId) {
			if ($regionId > 0 && !isset($bids_not_load[$regionId])) {
				$region_ids[] = $regionId;
			}
		}

		if (count($region_ids) === 0) {
			return [];
		}

		$updates = $this->activityGateway->fetchAllForumUpdates($region_ids, $page, false);
		if ($ambassadorIds = $this->session->getMyAmbassadorRegionIds()) {
			$updates = array_merge($updates, $this->activityGateway->fetchAllForumUpdates($ambassadorIds, $page, true));
		}

		if (!empty($updates)) {
			$out = array();
			foreach ($updates as $u) {
				$forumTypeString = $u['bot_theme'] === 1 ? 'botforum' : 'forum';
				$ambPrefix = $u['bot_theme'] === 1 ? 'BOT' : '';

				$url = '/?page=bezirk&bid=' . (int)$u['bezirk_id'] . '&sub=' . $forumTypeString . '&tid=' . (int)$u['id'] . '&pid=' . (int)$u['last_post_id'] . '#tpost-' . (int)$u['last_post_id'];

				$out[] = [
					'type' => 'forum',
					'data' => [
						'fs_id' => (int)$u['foodsaver_id'],
						'fs_name' => $u['foodsaver_name'],
						'forum_href' => $url,
						'forum_name' => $u['name'],
						'region_name' => $ambPrefix . ' ' . $u['bezirk_name'],
						'desc' => $u['post_body'],
						'time' => $u['update_time'],
						'icon' => $this->imageService->img($u['foodsaver_photo'], 50),
						'time_ts' => $u['update_time_ts'],
						'quickreply' => '/xhrapp.php?app=bezirk&m=quickreply&bid=' . (int)$u['bezirk_id'] . '&tid=' . (int)$u['id'] . '&pid=' . (int)$u['last_post_id'] . '&sub=' . $$forumTypeString
					]
				];
			}

			return $out;
		}

		return [];
	}

	public function loadStoreUpdates($page = 0)
	{
		if ($this->session->getMyBetriebIds() && $ret = $this->activityGateway->fetchAllStoreUpdates($this->session->id(), $page)) {
			$out = array();
			foreach ($ret as $r) {
				$out[] = [
					'type' => 'store',
					'data' => [
						'fs_id' => $r['foodsaver_id'],
						'fs_name' => $r['foodsaver_name'],
						'store_id' => $r['betrieb_id'],
						'store_name' => $r['betrieb_name'],
						'desc' => $r['text'],
						'time' => $r['update_time'],
						'icon' => $this->imageService->img($r['foodsaver_photo'], 50),
						'time_ts' => $r['update_time_ts']
					]
				];
			}

			return $out;
		}

		return false;
	}

	public function getBuddies()
	{
		if ($bids = $this->session->get('buddy-ids')) {
			return $this->activityGateway->fetchAllBuddies($bids);
		}

		return false;
	}
}
