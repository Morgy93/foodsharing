<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Core\DBConstants\Bell\BellType;
use Foodsharing\Modules\Core\DBConstants\Region\WorkgroupFunction;
use Foodsharing\Modules\Core\DBConstants\Report\ReportType;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Group\GroupFunctionGateway;
use Foodsharing\Modules\Report\ReportGateway;
use Foodsharing\Permissions\ReportPermissions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class ReportRestController extends AbstractFOSRestController
{
    private BellGateway $bellGateway;
    private FoodsaverGateway $foodsaverGateway;
    private Session $session;
    private ReportGateway $reportGateway;
    private ReportPermissions $reportPermissions;
    private GroupFunctionGateway $groupFunctionGateway;

    // literal constants
    private const NOT_LOGGED_IN = 'not logged in';

    public function __construct(
        Session $session,
        BellGateway $bellGateway,
        FoodsaverGateway $foodsaverGateway,
        ReportGateway $reportGateway,
        ReportPermissions $reportPermissions,
        GroupFunctionGateway $groupFunctionGateway
    ) {
        $this->session = $session;
        $this->bellGateway = $bellGateway;
        $this->reportGateway = $reportGateway;
        $this->reportPermissions = $reportPermissions;
        $this->groupFunctionGateway = $groupFunctionGateway;
        $this->foodsaverGateway = $foodsaverGateway;
    }

    /**
     * @OA\Tag(name="report")
     *
     * @param int $regionId for which region the reports should be returned
     *
     * @Rest\Get("report/region/{regionId}", requirements={"regionId" = "\d+"})
     *
     * An admin of a reportgroup gets all reports from the home district. Excluded are
     * reports with participation from same admins
     *
     * Admins of arbitrationgroup only gets the reports that have participation from
     * admins of report group.
     *
     * A user can't be admin of both groups.
     */
    public function listReportsForRegionAction(int $regionId): Response
    {
        if (!$this->session->mayRole()) {
            throw new UnauthorizedHttpException('', self::NOT_LOGGED_IN);
        }

        if (!$this->reportPermissions->mayAccessReportsForRegion($regionId)) {
            throw new AccessDeniedHttpException();
        }

        $regions = [$regionId];

        $excludeIDs = null;
        $reportGroup = $this->groupFunctionGateway->getRegionFunctionGroupId($regionId, WorkgroupFunction::REPORT);
        $onlyWithIds = null;
        $reportAdminIDs = null;
        if (!empty($reportGroup)) {
            $reportAdminIDs = $this->groupFunctionGateway->getFsAdminIdsFromGroup($reportGroup);
            if (in_array($this->session->id(), $reportAdminIDs)) {
                $excludeIDs = $reportAdminIDs;
            }
        }
        $arbitrationAdminIDs = null;
        $arbitrationGroup = $this->groupFunctionGateway->getRegionFunctionGroupId($regionId, WorkgroupFunction::ARBITRATION);
        if (!empty($arbitrationGroup)) {
            $arbitrationAdminIDs = $this->groupFunctionGateway->getFsAdminIdsFromGroup($arbitrationGroup);
            if (in_array($this->session->id(), $arbitrationAdminIDs)) {
                $excludeIDs = $arbitrationAdminIDs;
                if (!empty($reportAdminIDs)) {
                    $onlyWithIds = $reportAdminIDs;
                }
            }
        }

        if (!empty($reportGroup) &&
            !empty($arbitrationGroup)) {
            if (in_array($this->session->id(), $arbitrationAdminIDs) &&
                in_array($this->session->id(), $reportAdminIDs)) {
                throw new AccessDeniedHttpException();
            }
        }

        $reports = $this->reportGateway->getReportsByReporteeRegions($regions, $excludeIDs, $onlyWithIds);

        return $this->handleView($this->view(['data' => $reports], 200));
    }

    /**
     * Adds a new report. The reportedId must not be empty.
     *
     * @OA\Tag(name="report")
     * @Rest\Post("report")
     * @Rest\RequestParam(name="reportedId", nullable=true)
     * @Rest\RequestParam(name="reporterId", nullable=true)
     * @Rest\RequestParam(name="reasonId", nullable=true)
     * @Rest\RequestParam(name="reason", nullable=true)
     * @Rest\RequestParam(name="message", nullable=true)
     * @Rest\RequestParam(name="storeId", nullable=true)
     */
    public function addReportAction(ParamFetcher $paramFetcher): Response
    {
        if (!$this->session->mayRole()) {
            throw new UnauthorizedHttpException('', self::NOT_LOGGED_IN);
        }
        $this->reportGateway->addBetriebReport(
            $paramFetcher->get('reportedId'),
            $paramFetcher->get('reporterId'),
            ReportType::GOALS_REPORT,
            $paramFetcher->get('reasonId'),
            $paramFetcher->get('reason'),
            $paramFetcher->get('message'),
            $paramFetcher->get('storeId')
        );

        $reportedFs = $this->foodsaverGateway->getFoodsaverBasics($paramFetcher->get('reportedId'));
        $bellData = Bell::create(
            'new_report_title',
            'report_reason',
            'far fa-life-ring fa-fw',
            ['href' => '/?page=report&bid=' . $reportedFs['bezirk_id']],
            [
                'name' => $reportedFs['name'] . ' ' . $reportedFs['nachname'],
                'reason' => $paramFetcher->get('reason')
            ],
            BellType::createIdentifier(BellType::NEW_REPORT, $reportedFs['id']),
            true
        );

        $reportBellRecipients = 0;
        $regionReportGroupId = $this->groupFunctionGateway->getRegionFunctionGroupId($reportedFs['bezirk_id'], WorkgroupFunction::REPORT);
        if ($regionReportGroupId) {
            $reportBellRecipients = $this->groupFunctionGateway->getFsAdminIdsFromGroup($regionReportGroupId);
            if (in_array($reportedFs['id'], $reportBellRecipients)
             || in_array($paramFetcher->get('reporterId'), $reportBellRecipients)) {
                $regionArbitrationGroupId = $this->groupFunctionGateway->getRegionFunctionGroupId($reportedFs['bezirk_id'], WorkgroupFunction::ARBITRATION);
                $reportBellRecipients = $this->groupFunctionGateway->getFsAdminIdsFromGroup($regionArbitrationGroupId);
            }
            $this->bellGateway->addBell($reportBellRecipients, $bellData);
        }

        return $this->handleView($this->view([$reportBellRecipients], 200));
    }
}
