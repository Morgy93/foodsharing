<?php

namespace Foodsharing\Services;

use Foodsharing\Helpers\EmailHelper;
use Foodsharing\Helpers\TranslationHelper;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\FoodSharePoint\FoodSharePointGateway;

final class NotificationService
{
	private $bellGateway;
	private $foodSharePointGateway;
	private $sanitizerService;
	private $emailHelper;
	private $translationHelper;

	public function __construct(
		BellGateway $bellGateway,
		FoodSharePointGateway $foodSharePoint,
		SanitizerService $sanitizerService,
		EmailHelper $emailHelper,
		TranslationHelper $translationHelper
	) {
		$this->bellGateway = $bellGateway;
		$this->foodSharePointGateway = $foodSharePoint;
		$this->sanitizerService = $sanitizerService;
		$this->emailHelper = $emailHelper;
		$this->translationHelper = $translationHelper;
	}

	public function newFoodSharePointPost(int $foodSharePointId)
	{
		$foodSharePoint = $this->foodSharePointGateway->getFoodSharePoint($foodSharePointId);
		if (!$foodSharePoint) {
			return;
		}
		$post = $this->foodSharePointGateway->getLastFoodSharePointPost($foodSharePointId);

		$this->sendEmailNotification($foodSharePoint, $post);
		$this->sendBellNotification($foodSharePoint, $post);
	}

	private function sendEmailNotification(array $foodSharePoint, array $post): void
	{
		$eMailFollowers = $this->foodSharePointGateway->getEmailFollower($foodSharePoint['id']);
		if (!$eMailFollowers || empty($post['attach'])) {
			return;
		}

		$attach = json_decode($post['attach'], true);
		if (!isset($attach['image']) && empty($attach['image'])) {
			return;
		}

		$body = nl2br($post['body']);
		foreach ($attach['image'] as $img) {
			$body .= '
			<div>
				<img src="' . BASE_URL . '/images/wallpost/medium_' . $img['file'] . '" />
			</div>';
		}

		foreach ($eMailFollowers as $f) {
			$this->emailHelper->tplMail('foodSharePoint/new_message', $f['email'], array(
				'link' => BASE_URL . '/?page=fairteiler&sub=ft&id=' . (int)$foodSharePoint['id'],
				'name' => $f['name'],
				'anrede' => $this->translationHelper->genderWord($f['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r'),
				'fairteiler' => $foodSharePoint['name'],
				'post' => $body
			));
		}
	}

	private function sendBellNotification(array $foodSharePoint, array $post): void
	{
		$infoFollowers = $this->foodSharePointGateway->getInfoFollowerIds($foodSharePoint['id']);
		if (!$infoFollowers) {
			return;
		}

		$followersWithoutPostAuthor = array_diff($infoFollowers, [$post['fs_id']]);
		$this->bellGateway->addBell(
			$followersWithoutPostAuthor,
			'ft_update_title',
			'ft_update',
			'img img-recycle yellow',
			array('href' => '/?page=fairteiler&sub=ft&id=' . $foodSharePoint['id']),
			array('name' => $foodSharePoint['name'], 'user' => $post['fs_name'], 'teaser' => $this->sanitizerService->tt($post['body'], 100)),
			'fairteiler-' . $foodSharePoint['id']
		);
	}
}
