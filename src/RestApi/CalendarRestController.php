<?php

namespace Foodsharing\RestApi;

use Carbon\Carbon;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Profile\ProfileGateway;
use Foodsharing\Modules\Settings\SettingsGateway;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Jsvrcek\ICS\Model\CalendarEvent;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Welp\IcalBundle\Factory\Factory;
use Welp\IcalBundle\Response\CalendarResponse;

/**
 * Provides endpoints for exporting pickup dates to iCal and managing access tokens.
 */
class CalendarRestController extends AbstractFOSRestController
{
	private Session $session;
	private SettingsGateway $settingsGateway;
	private ProfileGateway $profileGateway;
	private Factory $icalFactory;

	public function __construct(
		Session $session,
		SettingsGateway $settingsGateway,
		ProfileGateway $profileGateway
	) {
		$this->session = $session;
		$this->settingsGateway = $settingsGateway;
		$this->profileGateway = $profileGateway;

		$this->icalFactory = new Factory();
	}

	/**
	 * Returns the user's future pickup dates as iCal.
	 *
	 * @OA\Parameter(name="token", in="path", @OA\Schema(type="string"), description="Access tken")
	 * @OA\Response(response="200", description="Success.")
	 * @OA\Response(response="403", description="Insufficient permissions or invalid token.")
	 * @OA\Tag(name="calendar")
	 *
	 * @Rest\Get("calendar/{token}")
	 * @Rest\QueryParam(name="token", description="Access token")
	 */
	public function listPickupDatesAction(string $token): Response
	{
		$userId = $this->session->id();
		if (!$userId) {
			throw new HttpException(403);
		}

		// check access token
		$existingToken = $this->settingsGateway->getApiToken($userId);
		if (empty($token) || empty($existingToken) || $token !== $existingToken) {
			throw new HttpException(403);
		}

		// create iCal of all future pickup dates
		$dates = $this->profileGateway->getNextDates($userId);
		$calendar = $this->icalFactory->createCalendar();
		foreach ($dates as $date) {
			$calendar->addEvent($this->createPickupEvent($date, $userId));
		}

		return new CalendarResponse($calendar, 200, []);
	}

	private function createPickupEvent(array $pickup, int $userId): CalendarEvent
	{
		$start = Carbon::createFromTimestamp($pickup['date_ts']);

		$summary = $pickup['betrieb_name'] . ' Abholung';
		if (!$pickup['confirmed']) {
			$summary .= ' (unbestätigt)';
		}

		$event = $this->icalFactory->createCalendarEvent();
		$event->setStart($start);
		$event->setEnd($start->clone()->addMinutes(30));
		$event->setSummary($summary);
		$event->setUid($userId . $pickup['date_ts'] . '@fetch.foodsharing.de');
		$event->setDescription('foodsharing Abholung bei ' . $pickup['betrieb_name']);
		$event->setUrl(BASE_URL . '/?page=fsbetrieb&id=' . $pickup['betrieb_id']);

		return $event;
	}
}
