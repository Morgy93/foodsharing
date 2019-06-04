<?php

namespace Foodsharing\Services;

use Carbon\Carbon;
use Foodsharing\Helpers\EmailHelper;
use Foodsharing\Helpers\TranslationHelper;
use Foodsharing\Lib\Db\Mem;
use Foodsharing\Lib\WebSocketSender;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Message\MessageGateway;
use Foodsharing\Modules\Store\StoreGateway;

class MessageService
{
	private $emailHelper;
	private $foodsaverGateway;
	private $mem;
	private $messageGateway;
	private $storeGateway;
	private $translationHelper;
	private $webSocketSender;

	public function __construct(EmailHelper $emailHelper, FoodsaverGateway $foodsaverGateway, Mem $mem, MessageGateway $messageGateway, StoreGateway $storeGateway, TranslationHelper $translationHelper, WebSocketSender $webSocketSender)
	{
		$this->emailHelper = $emailHelper;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->mem = $mem;
		$this->messageGateway = $messageGateway;
		$this->storeGateway = $storeGateway;
		$this->translationHelper = $translationHelper;
		$this->webSocketSender = $webSocketSender;
	}

	private function sendNewMessageNotificationEmail($recipient, $conversation_id, $msg, $templateData)
	{
		if (!$this->mem->userOnline($recipient['id'])) {
			/* skip repeated notification emails in a short interval */
			if (!isset($_SESSION['lastMailMessage']) || !is_array($sessdata = $_SESSION['lastMailMessage'])) {
				$sessdata = array();
			}

			if (!isset($sessdata[$recipient['id']]) || (time() - $sessdata[$recipient['id']]) > 600) {
				$sessdata[$recipient['id']] = time();

				$templateData = array_merge($templateData, [
					'anrede' => $this->translationHelper->genderWord($recipient['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r'),
					'name' => $recipient['name'],
				]);

				$this->emailHelper->tplMail($templateData['emailTemplate'], $recipient['email'], $templateData);
			}
			$_SESSION['lastMailMessage'] = $sessdata;
		}
	}

	private function getNotificationTemplateData(int $conversationId, array $sender, string $body, array $members): array
	{
		$data = [];
		$storeName = $this->storeGateway->getStoreNameByConversationId($conversationId);
		if (!is_null($storeName)) {
			$data['emailTemplate'] = 'chat/message_store';
			$data['storeName'] = $storeName;
		} else {
			if (count($members) > 2) {
				$data['emailTemplate'] = 'chat/message_group';
				$data['chatName'] =
					implode(', ',
						array_column(
							array_filter($members,
								function ($m) use ($sender) {
									return $m['id'] != $sender['id'];
								}),
							'name'
						)
					);
			} else {
				$data['emailTemplate'] = 'chat/message';
			}
		}

		$data['sender'] = $sender['name'];
		$data['message'] = $body;
		$data['link'] = BASE_URL . '/?page=msg&cid=' . $conversationId;

		return $data;
	}

	private function sendNewMessageNotifications(int $conversationId, int $senderId, string $body, Carbon $time, int $messageId)
	{
		if ($members = $this->messageGateway->listConversationMembersWithProfile($conversationId)) {
			$user_ids = array_column($members, 'id');
			$sender = $this->foodsaverGateway->getFoodsaverDetails($senderId);

			$this->webSocketSender->sendSockMulti($user_ids, 'conv', 'push', array(
				'id' => $messageId,
				'cid' => $conversationId,
				'fs_id' => $senderId,
				'fs_name' => $sender['name'],
				'fs_photo' => $sender['photo'],
				'body' => $body,
				'time' => $time->toDateTimeString()
			));

			$notificationTemplateData = $this->getNotificationTemplateData($conversationId, $sender, $body, $members);
			foreach ($members as $m) {
				if ($m['id'] != $senderId) {
					if ($m['infomail_message']) {
						$this->sendNewMessageNotificationEmail($m, $conversationId, $body, $notificationTemplateData);
					}
				}
			}
		}
	}

	public function sendMessage(int $conversationId, int $senderId, string $body): ?int
	{
		$body = trim($body);
		/* todo: we do want to get rid of this line */
		$body = htmlentities($body);
		if (!empty($body)) {
			$time = Carbon::now();
			$messageId = $this->messageGateway->addMessage($conversationId, $senderId, $body, $time);
			$this->sendNewMessageNotifications($conversationId, $senderId, $body, $time, $messageId);

			return $messageId;
		}

		return null;
	}
}
