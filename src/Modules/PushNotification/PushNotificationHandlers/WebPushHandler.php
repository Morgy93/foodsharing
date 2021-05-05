<?php

namespace Foodsharing\Modules\PushNotification\PushNotificationHandlers;

use Foodsharing\Modules\PushNotification\Notification\MessagePushNotification;
use Foodsharing\Modules\PushNotification\Notification\PushNotification;
use Foodsharing\Modules\PushNotification\PushNotificationHandlerInterface;
use Minishlink\WebPush\Encryption;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\Utils;
use Minishlink\WebPush\WebPush;
use Symfony\Contracts\Translation\TranslatorInterface;

class WebPushHandler implements PushNotificationHandlerInterface
{
	private const typeIdentifier = 'webpush';
	private WebPush $webpush;
	private TranslatorInterface $translator;

	public function __construct(TranslatorInterface $translator)
	{
		$auth = [
			'VAPID' => [
				'subject' => $_SERVER['SERVER_NAME'] ?? '',
				'publicKey' => WEBPUSH_PUBLIC_KEY,
				'privateKey' => WEBPUSH_PRIVATE_KEY
			],
		];

		$this->webpush = new WebPush($auth);
		$this->translator = $translator;
	}

	/**
	 * @see PushNotificationHandlerInterface::getTypeIdentifier()
	 */
	public static function getTypeIdentifier(): string
	{
		return self::typeIdentifier;
	}

	/**
	 * @param string[] $subscriptionData an map of ID to subscription data in JSON format
	 */
	public function sendPushNotificationsToClients(array $subscriptionData, PushNotification $notification): array
	{
		$payload = $this->makePayload($notification);
		$deadSubscriptions = [];

		foreach ($subscriptionData as $subscriptionId => $subscriptionAsJson) {
			$subscriptionArray = json_decode($subscriptionAsJson, true);

			// Fix inconsistent definition of encoding by some clients
			$subscriptionArray['contentEncoding'] = $subscriptionArray['contentEncoding'] ?? 'aesgcm';

			$subscription = Subscription::create($subscriptionArray);

			$messageSentReport = $this->webpush->sendOneNotification($subscription, $payload);

			$endpoint = $messageSentReport->getEndpoint();

			if ($messageSentReport->isSubscriptionExpired()) {
				$deadSubscriptions[] = $subscriptionId;
			}

			// logging
			if (!$messageSentReport->isSuccess()) {
				error_log("Message failed to send for subscription {$endpoint}: {$messageSentReport->getReason()}");
			}
		}

		return $deadSubscriptions;
	}

	public function getServerInformation(): array
	{
		return ['key' => WEBPUSH_PUBLIC_KEY];
	}

	/**
	 * @return string - json formatted payload
	 */
	private function makePayload(PushNotification $notification): string
	{
		$payloadArray = [];

		if ($notification instanceof MessagePushNotification) {
			// set body
			$payloadArray['options']['body'] = $notification->getMessage()->body;
			// set time stamp
			$payloadArray['options']['timestamp'] = $notification->getMessage()->sentAt->getTimestamp() * 1000; // timestamp needs to be in milliseconds
			// set action
			$payloadArray['options']['data']['action'] = ['page' => 'conversations', 'params' => [$notification->getConversationId()]]; // this thing will be resolved to a url by urls.js on client side
			// Set title
			if ($notification->getConversationName() !== null) {
				$payloadArray['title'] = $this->translator->trans(
					'chat.notification_named_conversation',
					['{foodsaver}' => $notification->getAuthor()->name, '{conversation}' => $notification->getConversationName()]
				);
			} else {
				$payloadArray['title'] = $this->translator->trans(
					'chat.notification_unnamed_conversation',
					['{foodsaver}' => $notification->getAuthor()->name]
				);
			}
		} else {
			// Seems to be a PushNotification type we don't know, but luckily we can fall back on a simple text notification with just title and body
			$payloadArray['title'] = $notification->getTitle($this->translator);
			$payloadArray['options']['body'] = $notification->getBody($this->translator);
		}

		$payloadArray = $this->cropPayload($payloadArray);

		return json_encode($payloadArray);
	}

	/**
	 * Crops the payload body, so the payload doesn't exceed the safe string length for WebPush payloads.
	 *
	 * @param array $payload a payload array containing at least a 'body' key (because this is what will be cropped)
	 *
	 * @return array Payload that definitely has a sendable length
	 */
	private function cropPayload(array $payload): array
	{
		$overlappingChars = Utils::safeStrlen(json_encode($payload)) - Encryption::MAX_PAYLOAD_LENGTH;

		if ($overlappingChars <= 0) {
			return $payload;
		}

		// only cut the body, I assume that the rest is not the critical factor
		$payload['options']['body'] = substr($payload['options']['body'], 0, strlen($payload['options']['body']) - $overlappingChars - 3);
		$payload['options']['body'] .= '...';

		return $payload;
	}
}
