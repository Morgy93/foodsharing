<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\BigBlueButton;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Group\GroupGateway;
use Foodsharing\Modules\Group\GroupTransactions;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Unit\DTO\UserUnit;
use Foodsharing\Permissions\RegionPermissions;
use Foodsharing\RestApi\Models\Group\UserGroupModel;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class GroupRestController extends AbstractFOSRestController
{
    public function __construct(
        private GroupGateway $groupGateway,
        private Session $session,
        private RegionPermissions $regionPermissions,
        private GroupTransactions $groupTransactions
    ) {
    }

    /**
     * Delete a region or a working group.
     *
     * @OA\Response(response="200", description="Success")
     * @OA\Response(response="403", description="Insufficient permissions")
     * @OA\Response(response="409", description="Group still contains elements")
     * @OA\Tag(name="groups")
     * @Rest\Delete("groups/{groupId}", requirements={"groupId" = "\d+"})
     */
    public function deleteGroupAction(int $groupId): Response
    {
        if (!$this->session->id()) {
            throw new UnauthorizedHttpException('', 'not logged in');
        }
        if (!$this->regionPermissions->mayAdministrateRegions()) {
            throw new AccessDeniedHttpException();
        }

        // check if the group still contains elements
        if ($this->groupTransactions->hasSubElements($groupId)) {
            throw new ConflictHttpException();
        }

        $this->groupGateway->deleteGroup($groupId);

        return $this->handleView($this->view([], 200));
    }

    /**
     * Returns the join URL of a given groups conference.
     *
     * @OA\Tag(name="groups")
     * @Rest\Get("groups/{groupId}/conference", requirements={"groupId" = "\d+"})
     * @Rest\QueryParam(name="redirect", default="false", description="Should the response perform a 301 redirect to the actual conference?")
     */
    public function joinConferenceAction(RegionGateway $regionGateway, RegionPermissions $regionPermissions, BigBlueButton $bbb, int $groupId, ParamFetcher $paramFetcher): Response
    {
        if (!$this->session->mayRole()) {
            throw new UnauthorizedHttpException('');
        }
        if (!in_array($groupId, $this->session->listRegionIDs())) {
            throw new AccessDeniedHttpException();
        }
        $group = $regionGateway->getRegion($groupId);
        if (!$regionPermissions->hasConference($group['type'])) {
            throw new AccessDeniedHttpException('This region does not support conferences');
        }
        $key = 'region-' . $groupId;
        $conference = $bbb->createRoom($group['name'], $key);
        if (!$conference) {
            throw new HttpException(500, 'Conferences currently not available');
        }
        $data = [
            'dialin' => $conference['dialin'],
            'id' => $conference['id'],
        ];
        /* We do a 301 redirect directly to have less likeliness that the user forwards the BBB join URL as this is already personalized */
        if ($paramFetcher->get('redirect') == 'true') {
            return $this->redirect($bbb->joinURL($key, $this->session->user('name'), true));
        }
        /* Without the redirect, we return information about the conference */
        return $this->handleView($this->view($data, 200));
    }

    /**
     * Returns a list of all groups of the user.
     *
     * @OA\Tag(name="groups")
     * @OA\Tag(name="my")
     * @Rest\Get("user/current/groups")
     * @OA\Response(
     * 		response="200",
     * 		description="Success returns list of related groups of user",
     *      @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=UserGroupModel::class))
     *      )
     * )
     * @OA\Response(response="401", description="Not logged in.")
     */
    public function listMyWorkingGroups(): Response
    {
        if (!$this->session->mayRole()) {
            throw new UnauthorizedHttpException('');
        }
        $fsId = $this->session->id();

        $groups = $this->groupTransactions->getUserGroups($fsId);

        $rspGroups = array_map(fn (UserUnit $group): UserGroupModel => UserGroupModel::createFrom($group), $groups);

        return $this->handleView($this->view($rspGroups, 200));
    }
}
