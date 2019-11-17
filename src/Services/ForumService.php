<?php

namespace Foodsharing\Services;

use Foodsharing\Helpers\EmailHelper;
use Foodsharing\Helpers\TranslationHelper;
use Foodsharing\Lib\Db\Db;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Region\ForumFollowerGateway;
use Foodsharing\Modules\Region\ForumGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Core\DBConstants\Region\Type;

class ForumService
{
	private $forumGateway;
	private $regionGateway;
	private $foodsaverGateway;
	private $bellGateway;
	private $forumFollowerGateway;
	/* @var Db */
	private $model;
	private $session;
	private $sanitizerService;
	private $emailHelper;
	private $translationHelper;

	public function __construct(
		BellGateway $bellGateway,
		FoodsaverGateway $foodsaverGateway,
		ForumGateway $forumGateway,
		ForumFollowerGateway $forumFollowerGateway,
		Session $session,
		Db $model,
		RegionGateway $regionGateway,
		SanitizerService $sanitizerService,
		EmailHelper $emailHelper,
		TranslationHelper $translationHelper
	) {
		$this->bellGateway = $bellGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->forumGateway = $forumGateway;
		$this->forumFollowerGateway = $forumFollowerGateway;
		$this->session = $session;
		$this->model = $model;
		$this->regionGateway = $regionGateway;
		$this->sanitizerService = $sanitizerService;
		$this->emailHelper = $emailHelper;
		$this->translationHelper = $translationHelper;
	}

	public function url($regionId, $ambassadorForum, $threadId = null, $postId = null)
	{
		$url = '/?page=bezirk&bid=' . $regionId . '&sub=' . ($ambassadorForum ? 'botforum' : 'forum');
		if ($threadId) {
			$url .= '&tid=' . $threadId;
		}
		if ($postId) {
			$url .= '&pid=' . $postId . '#post' . $postId;
		}

		return $url;
	}

	public function notifyParticipantsViaBell($threadId, $authorId, $postId)
	{
		$posts = $this->forumGateway->listPosts($threadId);
		$info = $this->forumGateway->getThreadInfo($threadId);
		$regionName = $this->regionGateway->getRegionName($info['region_id']);

		$getFsId = function ($post) {
			return $post['author_id'];
		};
		$removeAuthorFsId = function ($id) use ($authorId) {
			return $id != $authorId;
		};
		$fsIds = array_map($getFsId, $posts);
		$fsIds = array_filter($fsIds, $removeAuthorFsId);
		$fsIds = array_unique($fsIds);

		$this->bellGateway->addBell(
			$fsIds,
			'forum_answer_title',
			'forum_answer',
			'fas fa-comments',
			['href' => $this->url($info['region_id'], $info['ambassador_forum'], $threadId, $postId)],
			['user' => $this->session->user('name'), 'forum' => $regionName],
			'forum-post-' . $postId
		);
	}

	public function addPostToThread($fsId, $threadId, $body)
	{
		$rawBody = $body;
		$pid = $this->forumGateway->addPost($fsId, $threadId, $body);
		$this->notifyFollowersNewPost($threadId, $rawBody, $fsId, $pid);
		$this->notifyParticipantsViaBell($threadId, $fsId, $pid);

		return $pid;
	}

	public function createThread($fsId, $title, $body, $region, $ambassadorForum, $isActive)
	{
		$threadId = $this->forumGateway->addThread($fsId, $region['id'], $title, $body, $isActive, $ambassadorForum);
		if (!$isActive) {
			$this->notifyAdminsModeratedThread($region, $threadId, $body);
		} else {
			$this->notifyUsersNewThread($region, $threadId, $ambassadorForum);
		}

		return $threadId;
	}

	public function activateThread($threadId, $region = null, $ambassadorForum = false)
	{
		$this->forumGateway->activateThread($threadId);
		/* TODO: this needs proper first activation handling */
		if ($region) {
			$this->notifyUsersNewThread($region, $threadId, $ambassadorForum);
		}
	}

	public function notificationMail($recipients, $tpl, $data)
	{
		foreach ($recipients as $recipient) {
			$this->emailHelper->tplMail(
				$tpl,
				$recipient['email'],
				array_merge($data,
					[
						'anrede' => $this->translationHelper->genderWord($recipient['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r'),
						'name' => $recipient['name']
					])
			);
		}
	}

	public function notifyFollowersNewPost($threadId, $rawPostBody, $postFrom, $postId)
	{
		if ($follower = $this->forumFollowerGateway->getThreadFollower($this->session->id(), $threadId)) {
			$info = $this->forumGateway->getThreadInfo($threadId);
			$poster = $this->model->getVal('name', 'foodsaver', $this->session->id());
			$data = [
				'link' => BASE_URL . $this->url($info['region_id'], $info['ambassador_forum'], $threadId, $postId),
				'thread' => $info['title'],
				'post' => $this->sanitizerService->markdownToHtml($rawPostBody),
				'poster' => $poster
			];
			$this->notificationMail($follower, 'forum/answer', $data);
		}
	}

	private function notifyAdminsModeratedThread($region, $threadId, $rawPostBody)
	{
		$theme = $this->model->getValues(array('foodsaver_id', 'name'), 'theme', $threadId);
		$poster = $this->model->getVal('name', 'foodsaver', $theme['foodsaver_id']);

		if ($foodsaver = $this->foodsaverGateway->getBotschafter($region['id'])) {
			$data = [
				'link' => BASE_URL . $this->url($region['id'], false, $threadId),
				'thread' => $theme['name'],
				'post' => $this->sanitizerService->markdownToHtml($rawPostBody),
				'poster' => $poster,
				'bezirk' => $region['name'],
			];

			$this->notificationMail($foodsaver, 'forum/activation', $data);
		}
	}

	private function notifyUsersNewThread(array $region, int $threadId, bool $ambassadorForum)
	{
		$regionType = $this->regionGateway->getType($region['id']);
		if (!$ambassadorForum && in_array($regionType, [Type::COUNTRY, Type::FEDERAL_STATE])) {
			return;
		}

		$theme = $this->model->getValues(array('foodsaver_id', 'name', 'last_post_id'), 'theme', $threadId);
		$body = $this->model->getVal('body', 'theme_post', $theme['last_post_id']);

		$poster = $this->model->getVal('name', 'foodsaver', $theme['foodsaver_id']);

		if ($ambassadorForum) {
			$recipients = $this->foodsaverGateway->getBotschafter($region['id']);
		} else {
			$recipients = $this->foodsaverGateway->listActiveWithFullNameByRegion($region['id']);
		}

		$data = [
			'bezirk' => $region['name'],
			'poster' => $poster,
			'thread' => $theme['name'],
			'link' => BASE_URL . $this->url($region['id'], $ambassadorForum, $threadId),
			'post' => $this->sanitizerService->markdownToHtml($body),
			];
		$this->notificationMail($recipients,
			$ambassadorForum ? 'forum/new_region_ambassador_message' : 'forum/new_message', $data);
	}

	public function addReaction($fsId, $postId, $key)
	{
		if (!$fsId || !$postId || !$key) {
			throw new \InvalidArgumentException();
		}
		$this->forumGateway->addReaction($postId, $fsId, $key);
	}

	public function removeReaction($fsId, $postId, $key)
	{
		if (!$fsId || !$postId || !$key) {
			throw new \InvalidArgumentException();
		}
		$this->forumGateway->removeReaction($postId, $fsId, $key);
	}
}
