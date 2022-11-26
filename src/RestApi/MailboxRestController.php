<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Mailbox\MailboxFolder;
use Foodsharing\Modules\Mailbox\MailboxGateway;
use Foodsharing\Permissions\MailboxPermissions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class MailboxRestController extends AbstractFOSRestController
{
	private MailboxGateway $mailboxGateway;
	private MailboxPermissions $mailboxPermissions;
	private Session $session;

	public function __construct(
		MailboxGateway $mailboxGateway,
		MailboxPermissions $mailboxPermissions,
		Session $session
	) {
		$this->mailboxGateway = $mailboxGateway;
		$this->mailboxPermissions = $mailboxPermissions;
		$this->session = $session;
	}

	/**
	 * Changes the unread status of an email. This does not care about the previous status, i.e. setting a
	 * read email to read will still result in a 'success' response.
	 *
	 * @OA\Tag(name="emails")
	 * @Rest\Patch("emails/{emailId}/{status}", requirements={"emailId" = "\d+", "status" = "[0-1]"})
	 * @OA\Parameter(name="emailId", in="path", @OA\Schema(type="integer"), description="which email to modify")
	 * @OA\Parameter(name="status", in="path", @OA\Schema(type="integer"), description="either 0 for unread or 1 for read")
	 * @OA\Response(response=Response::HTTP_OK, description="Success.")
	 * @OA\Response(response=Response::HTTP_UNAUTHORIZED, description="Not logged in.")
	 * @OA\Response(response=Response::HTTP_BAD_REQUEST, description="Invalid status.")
	 * @OA\Response(response=Response::HTTP_FORBIDDEN, description="Insufficient permissions to modify the email.")
	 * @OA\Response(response=Response::HTTP_NOT_FOUND, description="Email does not exist.")
	 */
	public function setEmailStatusAction(int $emailId, int $status): Response
	{
		if (!$this->session->id()) {
			throw new UnauthorizedHttpException('');
		}
		if (!$this->mailboxPermissions->mayMessage($emailId)) {
			throw new AccessDeniedHttpException();
		}

		$this->mailboxGateway->setRead($emailId, $status);

		return $this->handleView($this->view([], Response::HTTP_OK));
	}

	/**
	 * Moves an email to the trash folder or deletes it, if it is already in the trash.
	 *
	 * @OA\Tag(name="emails")
	 * @Rest\Delete("emails/{emailId}", requirements={"emailId" = "\d+"})
	 * @OA\Parameter(name="emailId", in="path", @OA\Schema(type="integer"), description="which email to delete")
	 * @OA\Response(response=Response::HTTP_OK, description="Success.")
	 * @OA\Response(response=Response::HTTP_UNAUTHORIZED, description="Not logged in.")
	 * @OA\Response(response=Response::HTTP_FORBIDDEN, description="Insufficient permissions to delete the email.")
	 */
	public function deleteEmailAction(int $emailId): Response
	{
		// check permission
		if (!$this->session->id()) {
			throw new UnauthorizedHttpException('');
		}
		if (!$this->mailboxPermissions->mayMessage($emailId)) {
			throw new AccessDeniedHttpException();
		}

		// move or delete the email
		$folder = $this->mailboxGateway->getMailFolderId($emailId);
		if ($folder == MailboxFolder::FOLDER_TRASH) {
			$this->mailboxGateway->deleteMessage($emailId);
		} else {
			$this->mailboxGateway->move($emailId, MailboxFolder::FOLDER_TRASH);
		}

		return $this->handleView($this->view([], Response::HTTP_OK));
	}

	/**
	 * Returns the number of unread mails for the sending user.
	 *
	 * @OA\Tag(name="emails")
	 * @Rest\Get("emails/unread-count")
	 * @OA\Response(response=Response::HTTP_OK, description="Success.")
	 * @OA\Response(response=Response::HTTP_UNAUTHORIZED, description="Not logged in.")
	 */
	public function getUnreadMailCountAction(): Response
	{
		if (!$this->session->id()) {
			throw new UnauthorizedHttpException('', 'Not logged in.');
		}
		$unread = $this->mailboxGateway->getUnreadMailCount($this->session);

		return $this->handleView($this->view($unread, Response::HTTP_OK));
	}
}
