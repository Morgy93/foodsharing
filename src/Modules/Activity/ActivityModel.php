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

	public function loadEventWallUpdates(int $page): array
	{
		$updates = $this->activityGateway->fetchAllEventUpdates($this->session->id(), $page);
		$out = [];

		foreach ($updates as $u) {
			$replyUrl = '/xhrapp.php?app=wallpost&m=quickreply&table=event&id=' . (int)$u['event_id'];

			$out[] = [
				'type' => 'event',
				'data' => [
					'desc' => $u['body'],
					'event_id' => $u['event_id'],
					'event_name' => $u['name'],
					'fs_id' => $u['fs_id'],
					'fs_name' => $u['fs_name'],
					'gallery' => $u['gallery'] ?? [],
					'icon' => $this->imageService->img($u['fs_photo'], 50),
					'source' => $u['event_region'],
					'time' => $u['time'],
					'time_ts' => $u['time_ts'],
					'quickreply' => $replyUrl
				]
			];
		}

		return $out;
	}

	public function loadFoodSharePointWallUpdates(int $page): array
	{
		$updates = $this->activityGateway->fetchAllFoodSharePointWallUpdates($this->session->id(), $page);
		$out = [];

		foreach ($updates as $u) {
			// This would send updates to all subscribers, is it really needed?
			$replyUrl = '/xhrapp.php?app=wallpost&m=quickreply&table=fairteiler&id=' . (int)$u['fsp_id'];

			$out[] = [
				'type' => 'foodsharepoint',
				'data' => [
					'desc' => $u['body'],
					'fsp_id' => $u['fsp_id'],
					'fsp_name' => $u['name'],
					'fs_id' => $u['fs_id'],
					'fs_name' => $u['fs_name'],
					'gallery' => $u['gallery'] ?? [],
					'icon' => $this->imageService->img($u['fs_photo'], 50),
					'region_id' => $u['region_id'],
					'source' => $u['fsp_location'],
					'time' => $u['time'],
					'time_ts' => $u['time_ts']
				]
					// 'quickreply' => $replyUrl
			];
		}

		return $out;
	}

	public function loadFriendWallUpdates(int $page, array $hidden_ids): array
	{
		$buddy_ids = [];

		if ($b = $this->session->get('buddy-ids')) {
			$buddy_ids = $b;
		}

		$buddy_ids[$this->session->id()] = $this->session->id();

		$bids = [];
		foreach ($buddy_ids as $id) {
			if (!isset($hidden_ids[$id])) {
				$bids[] = $id;
			}
		}

		if ($updates = $this->activityGateway->fetchAllFriendWallUpdates($bids, $page)) {
			$out = [];
			foreach ($updates as $u) {
				$is_own = $u['fs_id'] === $this->session->id();

				$out[] = [
					'type' => 'friendWall',
					'data' => [
						'desc' => $u['body'],
						'fs_id' => $u['fs_id'],
						'fs_name' => $u['fs_name'],
						'gallery' => $u['gallery'] ?? [],
						'icon' => $this->imageService->img($u['fs_photo'], 50),
						'is_own' => $is_own ? '_own' : null,
						'source' => $u['fs_name'],
						'time' => $u['time'],
						'time_ts' => $u['time_ts']
					]
				];
			}

			return $out;
		}

		return [];
	}

	public function loadMailboxUpdates(int $page, array $hidden_ids): array
	{
		if ($boxes = $this->mailboxGateway->getBoxes($this->session->isAmbassador(), $this->session->id(), $this->session->may('bieb'))) {
			$mb_ids = [];
			foreach ($boxes as $b) {
				if (!isset($hidden_ids[$b['id']])) {
					$mb_ids[] = $b['id'];
				}
			}

			if (count($mb_ids) === 0) {
				return [];
			}

			if ($updates = $this->activityGateway->fetchAllMailboxUpdates($mb_ids, $page)) {
				$out = [];
				foreach ($updates as $u) {
					$sender = @json_decode($u['sender'], true);

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

		return [];
	}

	public function loadForumUpdates(int $page, array $hidden_ids): array
	{
		$myRegionIds = $this->session->listRegionIDs();
		$region_ids = [];
		if ($myRegionIds === [] || count($myRegionIds) === 0) {
			return [];
		}

		foreach ($myRegionIds as $regionId) {
			if ($regionId > 0 && !isset($hidden_ids[$regionId])) {
				$region_ids[] = $regionId;
			}
		}

		if (count($region_ids) === 0) {
			return [];
		}

		$updates = $this->activityGateway->fetchAllForumUpdates($region_ids, $page, false);
		if ($ambassadorIds = $this->session->getMyAmbassadorRegionIds()) {
			$botPosts = $this->activityGateway->fetchAllForumUpdates($ambassadorIds, $page, true);
			$updates = array_merge($updates, $botPosts);
		}

		if (!empty($updates)) {
			$out = [];
			foreach ($updates as $u) {
				$is_bot = $u['bot_theme'] === 1;

				$forumTypeString = $is_bot ? 'botforum' : 'forum';

				$replyUrl = '/xhrapp.php?app=bezirk&m=quickreply&bid=' . (int)$u['bezirk_id']
					. '&tid=' . (int)$u['id']
					. '&pid=' . (int)$u['last_post_id']
					. '&sub=' . $forumTypeString;

				$out[] = [
					'type' => 'forum',
					'data' => [
						'desc' => $u['post_body'],
						'fs_id' => (int)$u['foodsaver_id'],
						'fs_name' => $u['foodsaver_name'],
						'forum_name' => $u['name'],
						'forum_post' => (int)$u['last_post_id'],
						'forum_topic' => (int)$u['id'],
						'forum_type' => $forumTypeString,
						'icon' => $this->imageService->img($u['foodsaver_photo'], 50),
						'is_bot' => $is_bot ? '_bot' : null,
						'region_id' => (int)$u['bezirk_id'],
						'source' => $u['bezirk_name'],
						'time' => $u['update_time'],
						'time_ts' => $u['update_time_ts'],
						'quickreply' => $replyUrl
					]
				];
			}

			return $out;
		}

		return [];
	}

	public function loadStoreUpdates(int $page): array
	{
		if ($this->session->getMyBetriebIds() && $ret = $this->activityGateway->fetchAllStoreUpdates($this->session->id(), $page)) {
			$out = [];
			foreach ($ret as $r) {
				$out[] = [
					'type' => 'store',
					'data' => [
						'desc' => $r['text'],
						'fs_id' => $r['foodsaver_id'],
						'fs_name' => $r['foodsaver_name'],
						'icon' => $this->imageService->img($r['foodsaver_photo'], 50),
						'source' => $r['region_name'],
						'store_id' => $r['betrieb_id'],
						'store_name' => $r['betrieb_name'],
						'time' => $r['update_time'],
						'time_ts' => $r['update_time_ts']
					]
				];
			}

			return $out;
		}

		return [];
	}

	public function getBuddies()
	{
		if ($buddyIds = $this->session->get('buddy-ids')) {
			return $this->activityGateway->fetchAllBuddies($buddyIds);
		}

		return false;
	}
}
