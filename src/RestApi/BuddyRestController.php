<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Buddy\BuddyGateway;
use Foodsharing\Modules\Buddy\BuddyTransactions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;

use function in_array;

use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class BuddyRestController extends AbstractFOSRestController
{
	public function __construct(
		private readonly BuddyTransactions $buddyTransactions,
		private readonly BuddyGateway $buddyGateway,
		private readonly Session $session
	) {
	}

	/**
	 * Sends a buddy request to a user.
	 *
	 * @OA\Tag(name="buddy")
	 * @Rest\Put("buddy/{userId}", requirements={"userId" = "\d+"})
	 * @OA\Parameter(name="userId", in="path", @OA\Schema(type="integer"), description="which user to send the request to")
	 * @OA\Response(response=Response::HTTP_OK, description="Success.")
	 * @OA\Response(response=Response::HTTP_UNAUTHORIZED, description="Not logged in.")
	 * @OA\Response(response=Response::HTTP_BAD_REQUEST, description="Already buddy with that user.")
	 */
	public function sendRequestAction(int $userId): Response
	{
		if (!$this->session->id()) {
			throw new UnauthorizedHttpException('');
		}

		if (in_array($userId, $this->buddyGateway->listBuddyIds($this->session->id()))) {
			throw new BadRequestHttpException();
		}

		$isBuddyRequestForMe = $this->buddyGateway->buddyRequestedMe($userId, $this->session->id());
		if ($isBuddyRequestForMe) {
			$this->buddyTransactions->acceptBuddyRequest($userId);
		} else {
			$this->buddyTransactions->sendBuddyRequest($userId);
		}

		return $this->handleView($this->view(['isBuddy' => $isBuddyRequestForMe], Response::HTTP_OK));
	}
}
