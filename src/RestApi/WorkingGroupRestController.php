<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\WorkGroup\WorkGroupGateway;
use Foodsharing\Utility\EmailHelper;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class WorkingGroupRestController extends AbstractFOSRestController
{
	private WorkGroupGateway $workingGroupGateway;
	private EmailHelper $emailHelper;
	private Session $session;

	public function __construct(
		WorkGroupGateway $workingGroupGateway,
		EmailHelper $emailHelper,
		Session $session
	) {
		$this->workingGroupGateway = $workingGroupGateway;
		$this->emailHelper = $emailHelper;
		$this->session = $session;
	}

	/**
	 * Sends an email to the group's address.
	 *
	 * @OA\Response(response="200", description="Success")
	 * @OA\Response(response="400", description="Empty message")
	 * @OA\Response(response="401", description="Not logged in")
	 * @OA\Response(response="404", description="Group does not exist")
	 * @OA\Tag(name="workinggroups")
	 *
	 * @Rest\Post("workinggroups/{groupId}/email", requirements={"groupId" = "\d+"})
	 * @Rest\RequestParam(name="message", description="message content", nullable=false)
	 */
	public function sendEmailAction(int $groupId, ParamFetcher $paramFetcher): Response
	{
		if (!$this->session->may()) {
			throw new UnauthorizedHttpException('Not logged in');
		}

		$group = $this->workingGroupGateway->getGroup($groupId);
		if (!$group || empty($group['email'])) {
			throw new NotFoundHttpException();
		}

		$message = $paramFetcher->get('message');
		if (empty($message)) {
			throw new BadRequestHttpException();
		}

		// send the email to the group and the current user
		$userMail = $this->session->user('email');
		$recipients = [$group['email'], $userMail];
		$this->emailHelper->tplMail('general/workgroup_contact', $recipients, [
			'gruppenname' => $group['name'],
			'message' => $message,
			'username' => $this->session->user('name'),
			'userprofile' => BASE_URL . '/profile/' . $this->session->id(),
		], $userMail);

		return $this->handleView($this->view(['user' => $userMail, 'recipients' => $recipients], 200));
	}
}
