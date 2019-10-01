<?php

namespace Foodsharing\Services;

use Foodsharing\Helpers\EmailHelper;
use Foodsharing\Helpers\TranslationHelper;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\FairTeiler\FairTeilerGateway;

final class NotificationService
{
	private $bellGateway;
	private $fairteilerGateway;
	private $sanitizerService;
	private $emailHelper;
	private $translationHelper;

	public function __construct(
		BellGateway $bellGateway,
		FairTeilerGateway $fairTeilerGateway,
		SanitizerService $sanitizerService,
		EmailHelper $emailHelper,
		TranslationHelper $translationHelper
	) {
		$this->bellGateway = $bellGateway;
		$this->fairteilerGateway = $fairTeilerGateway;
		$this->sanitizerService = $sanitizerService;
		$this->emailHelper = $emailHelper;
		$this->translationHelper = $translationHelper;
	}

	public function newFairteilerPost(int $fairteilerId)
	{
		if ($ft = $this->fairteilerGateway->getFairteiler($fairteilerId)) {
			$post = $this->fairteilerGateway->getLastFairSharePointPost($fairteilerId);
			if ($followers = $this->fairteilerGateway->getEmailFollower($fairteilerId)) {
				$body = nl2br($post['body']);

				if (!empty($post['attach'])) {
					$attach = json_decode($post['attach'], true);
					if (isset($attach['image']) && !empty($attach['image'])) {
						foreach ($attach['image'] as $img) {
							$body .= '
							<div>
								<img src="' . BASE_URL . '/images/wallpost/medium_' . $img['file'] . '" />
							</div>';
						}
					}
				}

				foreach ($followers as $f) {
					$this->emailHelper->tplMail('fairSharePoint/new_message', $f['email'], array(
						'link' => BASE_URL . '/?page=fairteiler&sub=ft&id=' . (int)$fairteilerId,
						'name' => $f['name'],
						'anrede' => $this->translationHelper->genderWord($f['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r'),
						'fairteiler' => $ft['name'],
						'post' => $body
					));
				}
			}

			if ($followers = $this->fairteilerGateway->getInfoFollowerIds($fairteilerId)) {
				$followersWithoutPostAuthor = array_diff($followers, [$post['fs_id']]);
				$this->bellGateway->addBell(
					$followersWithoutPostAuthor,
					'ft_update_title',
					'ft_update',
					'img img-recycle yellow',
					array('href' => '/?page=fairteiler&sub=ft&id=' . (int)$fairteilerId),
					array('name' => $ft['name'], 'user' => $post['fs_name'], 'teaser' => $this->sanitizerService->tt($post['body'], 100)),
					'fairteiler-' . (int)$fairteilerId
				);
			}
		}
	}
}
