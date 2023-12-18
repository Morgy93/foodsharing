<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Buddy\BuddyGateway;
use Foodsharing\Modules\Buddy\BuddyTransactions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class BuddyRestController extends AbstractFOSRestController
{
    private BuddyTransactions $buddyTransactions;
    private BuddyGateway $buddyGateway;
    private Session $session;

    public function __construct(
        BuddyTransactions $buddyTransactions,
        BuddyGateway $buddyGateway,
        Session $session
    ) {
        $this->buddyTransactions = $buddyTransactions;
        $this->buddyGateway = $buddyGateway;
        $this->session = $session;
    }

    /**
     * Sends a buddy request to a user.
     *
     * @OA\Parameter(name="userId", in="path", @OA\Schema(type="integer"), description="which user to send the request to")
     * @OA\Response(response="200", description="Success.", @OA\Schema(type="object",
     *     @OA\Property(property="isBuddy", type="integer", description="whether the other user is now this user's buddy")
     * ))
     * @OA\Response(response="400", description="Already send a request to that user.")
     * @OA\Response(response="403", description="Insufficient permissions to send the request.")
     * @OA\Tag(name="buddy")
     * @Rest\Put("buddy/{userId}", requirements={"userId" = "\d+"})
     */
    public function sendRequestAction(int $userId): Response
    {
        if (!$this->session->id()) {
            throw new UnauthorizedHttpException('');
        }

        if ($this->buddyGateway->buddyRequestedUser($this->session->id(), $userId)) {
            throw new BadRequestHttpException('You cannot send mutliple requests');
        }

        $accepting = $this->buddyGateway->buddyRequestedUser($userId, $this->session->id());
        if ($accepting) {
            $this->buddyTransactions->acceptBuddyRequest($userId);
        } else {
            $this->buddyTransactions->sendBuddyRequest($userId);
        }

        return $this->handleView($this->view(['isBuddy' => $accepting], 200));
    }

    /**
     * Removes a buddy request to a user.
     *
     * @OA\Parameter(name="userId", in="path", @OA\Schema(type="integer"), description="which user to remove the request to")
     * @OA\Response(response="200", description="Success.", @OA\Schema(type="object"))
     * @OA\Response(response="400", description="Not currently requested to be a buddy of that user.")
     * @OA\Response(response="403", description="Insufficient permissions to send the request.")
     * @OA\Tag(name="buddy")
     * @Rest\Delete("buddy/{userId}", requirements={"userId" = "\d+"})
     */
    public function removeRequestAction(int $userId): Response
    {
        if (!$this->session->id()) {
            throw new UnauthorizedHttpException('');
        }

        if (!$this->buddyGateway->buddyRequestedUser($this->session->id(), $userId)) {
            throw new NotFoundHttpException('You cannot delete a request you did not send');
        }

        $this->buddyTransactions->removeBuddyRequest($userId);

        return $this->handleView($this->view(true, 200));
    }
}
