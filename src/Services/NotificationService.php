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
			http_response_code(404);

			return;
		}
		$post = $this->foodSharePointGateway->getLastFoodSharePointPost($foodSharePointId);

		$this->sendFoodSharePointEmailNotification($foodSharePoint, $post);
		$this->sendFoodSharePointBellNotification($foodSharePoint, $post);
	}

	private function sendFoodSharePointEmailNotification(array $foodSharePoint, array $post): void
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

	private function sendFoodSharePointBellNotification(array $foodSharePoint, array $post): void
	{
		$infoFollowers = $this->foodSharePointGateway->getInfoFollowerIds($foodSharePoint['id']);
		if (!$infoFollowers) {
			return;
		}

		$followersWithoutPostAuthor = array_diff($infoFollowers, [$post['fs_id']]);

		$bellIdentifier = 'fairteiler-' . $foodSharePoint['id'];
		$bellForThisFoodSharePointExists = $this->bellGateway->bellWithIdentifierExists($bellIdentifier);

		if (!$bellForThisFoodSharePointExists) {
			$this->bellGateway->addBell(
				$followersWithoutPostAuthor,
				'ft_update_title',
				'ft_update',
				'img img-recycle yellow',
				['href' => '/?page=fairteiler&sub=ft&id=' . $foodSharePoint['id']],
				[
					'name' => $foodSharePoint['name'],
					'user' => $post['fs_name'],
					'teaser' => $this->sanitizerService->tt($post['body'], 100)
				],
				$bellIdentifier
			);
		} else {
			$bellId = $this->bellGateway->getOneByIdentifier($bellIdentifier);
			$this->bellGateway->updateBell($bellId, [
				'vars' => [
					'name' => $foodSharePoint['name'],
					'user' => $post['fs_name'],
					'teaser' => $this->sanitizerService->tt($post['body'], 100)],
				'time' => new \DateTime()
			]);
			$this->bellGateway->makeSureFoodsaversSeeBell($bellId, $followersWithoutPostAuthor);
		}
	}
}
