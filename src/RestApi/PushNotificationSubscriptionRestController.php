<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\PushNotification\Notification\TestPushNotification;
use Foodsharing\Modules\PushNotification\PushNotificationGateway;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

class PushNotificationSubscriptionRestController extends AbstractFOSRestController
{
	/**
	 * @var PushNotificationGateway
	 */
	private $gateway;

	/**
	 * @var Session
	 */
	private $session;

	public function __construct(PushNotificationGateway $gateway, Session $session)
	{
		$this->gateway = $gateway;
		$this->session = $session;
	}

	/**
	 * @Rest\Get("pushnotification/{type}/server-information")
	 */
	public function getServerInformationAction(string $type)
	{
		if (!$this->gateway->hasHandlerFor($type)) {
			return $this->handleHttpStatus(404);
		}

		$view = $this->view($this->gateway->getServerInformation($type), 200);

		return $this->handleView($view);
	}

	/**
	 * @Rest\Post("pushnotification/{type}/subscription")
	 */
	public function subscribeAction(Request $request, string $type)
	{
		if (!$this->gateway->hasHandlerFor($type)) {
			return $this->handleHttpStatus(404);
		}

		if (!$this->session->may()) {
			return $this->handleHttpStatus(403);
		}

		$pushSubscription = $request->getContent();
		$foodsaverId = $this->session->id();

		$subscriptionId = $this->gateway->addSubscription($foodsaverId, $pushSubscription, $type);

		$this->gateway->sendPushNotificationsToFoodsaver($foodsaverId, new TestPushNotification());

		$result = json_decode($pushSubscription, true);
		$result['subscriptionId'] = $subscriptionId;

		return $this->handleView($this->view($result, 200));
	}

	/**
	 * @Rest\Delete("pushnotification/{type}/subscription/{subscriptionId}", requirements={"subscriptionId" = "\d+"})
	 */
	public function unsubscribeAction(string $type, int $subscriptionId)
	{
		if (!$this->gateway->hasHandlerFor($type)) {
			return $this->handleHttpStatus(404);
		}

		if (!$this->session->may()) {
			return $this->handleHttpStatus(403);
		}

		$foodsaverId = $this->session->id();

		$this->gateway->deleteSubscription($foodsaverId, $subscriptionId, $type);

		return $this->handleHttpStatus(200);
	}

	private function handleHttpStatus(int $statusCode)
	{
		return $this->handleView($this->view([], $statusCode));
	}
}
