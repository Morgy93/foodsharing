<?php

namespace Foodsharing\Modules\Region;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Map\MapConstants;
use Foodsharing\Modules\Core\DBConstants\Region\RegionOptionType;
use Foodsharing\Modules\Core\DBConstants\Unit\UnitType;
use Foodsharing\Modules\Event\EventGateway;
use Foodsharing\Modules\Mailbox\MailboxGateway;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Modules\Voting\VotingGateway;
use Foodsharing\Permissions\ForumPermissions;
use Foodsharing\Permissions\RegionPermissions;
use Foodsharing\Permissions\ReportPermissions;
use Foodsharing\Permissions\VotingPermissions;
use Foodsharing\Permissions\WorkGroupPermissions;
use Foodsharing\Utility\ImageHelper;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class RegionControl extends Control
{
    private array $region;
    private RegionGateway $gateway;
    private EventGateway $eventGateway;
    private ForumGateway $forumGateway;
    private ForumFollowerGateway $forumFollowerGateway;
    private FormFactoryInterface $formFactory;
    private ForumTransactions $forumTransactions;
    private ForumPermissions $forumPermissions;
    private RegionPermissions $regionPermissions;
    private StoreGateway $storeGateway;
    private ImageHelper $imageService;
    private ReportPermissions $reportPermissions;
    private MailboxGateway $mailboxGateway;
    private VotingGateway $votingGateway;
    private VotingPermissions $votingPermissions;
    private WorkGroupPermissions $workGroupPermission;

    private const DisplayAvatarListEntries = 30;

    /**
     * @required
     */
    public function setFormFactory(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function __construct(
        RegionView $view,
        EventGateway $eventGateway,
        ForumGateway $forumGateway,
        ForumFollowerGateway $forumFollowerGateway,
        ForumPermissions $forumPermissions,
        RegionPermissions $regionPermissions,
        ForumTransactions $forumTransactions,
        RegionGateway $gateway,
        ReportPermissions $reportPermissions,
        ImageHelper $imageService,
        MailboxGateway $mailboxGateway,
        VotingGateway $votingGateway,
        VotingPermissions $votingPermissions,
        WorkGroupPermissions $workGroupPermissions,
        StoreGateway $storeGateway
    ) {
        $this->view = $view;
        $this->gateway = $gateway;
        $this->eventGateway = $eventGateway;
        $this->forumPermissions = $forumPermissions;
        $this->regionPermissions = $regionPermissions;
        $this->forumGateway = $forumGateway;
        $this->forumFollowerGateway = $forumFollowerGateway;
        $this->forumTransactions = $forumTransactions;
        $this->reportPermissions = $reportPermissions;
        $this->imageService = $imageService;
        $this->mailboxGateway = $mailboxGateway;
        $this->votingGateway = $votingGateway;
        $this->votingPermissions = $votingPermissions;
        $this->workGroupPermission = $workGroupPermissions;
        $this->storeGateway = $storeGateway;
        parent::__construct();
    }

    private function mayAccessApplications(int $regionId): bool
    {
        return $this->forumPermissions->mayAccessAmbassadorBoard($regionId);
    }

    private function isHomeDistrict($region): bool
    {
        if ((int)$region['id'] === $this->session->getCurrentRegionId()) {
            return true;
        }

        return false;
    }

    private function regionViewData(array $region, ?string $activeSubpage): array
    {
        $regionId = (int)$region['id'];

        $avatarListEntry = function ($fs) {
            return [
                'user' => [
                    'id' => $fs['id'],
                    'name' => $fs['name'],
                    'sleep_status' => $fs['sleep_status']
                ],
                'size' => 50,
                'imageUrl' => $this->imageService->img($fs['photo'], 50, 'q')
            ];
        };

        return [
            'regionId' => $regionId,
            'name' => $this->region['name'],
            'isWorkGroup' => UnitType::isGroup($region['type']),
            'isHomeDistrict' => $this->isHomeDistrict($region),
            'isRegion' => !UnitType::isGroup($region['type']),
            'foodSaverCount' => $this->region['fs_count'],
            'foodSaverHomeDistrictCount' => $this->region['fs_home_count'],
            'foodSaverHasSleepingHatCount' => $this->region['sleeper_count'],
            'ambassadorCount' => $this->region['stat_botcount'],
            'storesCount' => $this->region['stat_betriebcount'],
            'storesCooperationCount' => $this->region['stat_korpcount'],
            'storesPickupsCount' => $this->region['stat_fetchcount'],
            'storesFetchedWeight' => round($this->region['stat_fetchweight']),
            'parent_id' => $this->region['parent_id'],
            'admins' => array_map($avatarListEntry, array_slice($this->region['botschafter'], 0, self::DisplayAvatarListEntries)),
            'welcomeAdmins' => array_map($avatarListEntry, array_slice($this->region['welcomeAdmins'], 0, self::DisplayAvatarListEntries)),
            'votingAdmins' => array_map($avatarListEntry, array_slice($this->region['votingAdmins'], 0, self::DisplayAvatarListEntries)),
            'fspAdmins' => array_map($avatarListEntry, array_slice($this->region['fspAdmins'], 0, self::DisplayAvatarListEntries)),
            'storesAdmins' => array_map($avatarListEntry, array_slice($this->region['storesAdmins'], 0, self::DisplayAvatarListEntries)),
            'reportAdmins' => array_map($avatarListEntry, array_slice($this->region['reportAdmins'], 0, self::DisplayAvatarListEntries)),
            'mediationAdmins' => array_map($avatarListEntry, array_slice($this->region['mediationAdmins'], 0, self::DisplayAvatarListEntries)),
            'arbitrationAdmins' => array_map($avatarListEntry, array_slice($this->region['arbitrationAdmins'], 0, self::DisplayAvatarListEntries)),
            'fsManagementAdmins' => array_map($avatarListEntry, array_slice($this->region['fsManagementAdmins'], 0, self::DisplayAvatarListEntries)),
            'prAdmins' => array_map($avatarListEntry, array_slice($this->region['prAdmins'], 0, self::DisplayAvatarListEntries)),
            'moderationAdmins' => array_map($avatarListEntry, array_slice($this->region['moderationAdmins'], 0, self::DisplayAvatarListEntries)),
            'boardAdmins' => array_map($avatarListEntry, array_slice($this->region['boardAdmins'], 0, self::DisplayAvatarListEntries)),
            'activeSubpage' => $activeSubpage,
        ];
    }

    public function index(Request $request, Response $response): void
    {
        if (!$this->session->mayRole()) {
            $this->routeHelper->goLoginAndExit();
        }

        $region_id = $request->query->getInt('bid', $_SESSION['client']['bezirk_id']);

        if ($this->session->mayBezirk($region_id) && ($region = $this->gateway->getRegionDetails($region_id))) {
            $big = [UnitType::BIG_CITY, UnitType::FEDERAL_STATE, UnitType::COUNTRY];
            $region['moderated'] = $region['moderated'] || in_array($region['type'], $big);
            $this->region = $region;
        } else {
            $this->flashMessageHelper->error($this->translator->trans('region.not-member'));
            $this->routeHelper->goAndExit('/?page=dashboard');
        }

        $this->pageHelper->addTitle($region['name']);
        $this->pageHelper->addBread($region['name'], '/?page=bezirk&bid=' . $region_id);

        switch ($request->query->get('sub')) {
            case 'botforum':
                if (!$this->forumPermissions->mayAccessAmbassadorBoard($region_id)) {
                    $this->routeHelper->goAndExit($this->forumTransactions->url($region_id, false));
                }
                $this->forum($request, $response, $region, true);
                break;
            case 'forum':
                $this->forum($request, $response, $region, false);
                break;
            case 'wall':
                if (!UnitType::isGroup($region['type'])) {
                    $this->flashMessageHelper->info($this->translator->trans('region.forum-redirect'));
                    $this->routeHelper->goAndExit('/?page=bezirk&bid=' . $region_id . '&sub=forum');
                } else {
                    $this->wall($request, $response, $region);
                }
                break;
            case 'fairteiler':
                $this->foodSharePoint($request, $response, $region);
                break;
            case 'events':
                $this->events($request, $response, $region);
                break;
            case 'applications':
                $this->applications($request, $response, $region);
                break;
            case 'members':
                $this->members($request, $response, $region);
                break;
            case 'statistic':
                $this->statistic($request, $response, $region);
                break;
            case 'polls':
                $this->polls($request, $response, $region);
                break;
            case 'options':
                $this->options($request, $response, $region);
                break;
            case 'pin':
                if (!$this->regionPermissions->maySetRegionPin($region_id) || UnitType::isGroup($region['type'])) {
                    $this->flashMessageHelper->info($this->translator->trans('region.restricted'));
                    $this->routeHelper->goAndExit($this->forumTransactions->url($region_id, false));
                }
                $this->pin($request, $response, $region);
                break;
            default:
                if (UnitType::isGroup($region['type'])) {
                    $this->routeHelper->goAndExit('/?page=bezirk&bid=' . $region_id . '&sub=wall');
                } else {
                    $this->routeHelper->goAndExit($this->forumTransactions->url($region_id, false));
                }
        }
    }

    private function wall(Request $request, Response $response, array $region): void
    {
        $viewdata = $this->regionViewData($region, $request->query->get('sub'));
        $viewdata['wall'] = ['module' => 'bezirk', 'wallId' => $region['id']];
        $response->setContent($this->render('pages/Region/wall.twig', $viewdata));
    }

    private function foodSharePoint(Request $request, Response $response, array $region): void
    {
        $this->pageHelper->addBread($this->translator->trans('terminology.fsp'), '/?page=bezirk&bid=' . $region['id'] . '&sub=fairteiler');
        $this->pageHelper->addTitle($this->translator->trans('terminology.fsp'));
        $params = $this->regionViewData($region, $request->query->get('sub'));
        $this->pageHelper->addContent($this->view->vueComponent('region-page', 'RegionPage', $params));
    }

    private function handleNewThreadForm(Request $request, array $region, $ambassadorForum, bool $postActiveWithoutModeration)
    {
        $this->pageHelper->addBread($this->translator->trans('forum.new_thread'));
        $data = CreateForumThreadData::create();
        $form = $this->formFactory->create(ForumCreateThreadForm::class, $data, ['postActiveWithoutModeration' => $postActiveWithoutModeration]);
        $form->handleRequest($request);
        if (
            $form->isSubmitted() && $form->isValid()
            && $this->forumPermissions->mayPostToRegion($region['id'], $ambassadorForum)
        ) {
            $threadId = $this->forumTransactions->createThread(
                $this->session->id(),
                $data->title,
                $data->body,
                $region,
                $ambassadorForum,
                $postActiveWithoutModeration,
                $postActiveWithoutModeration ? $data->sendMail : null
            );

            $this->forumFollowerGateway->followThreadByBell($this->session->id(), $threadId);

            if (!$postActiveWithoutModeration) {
                $this->flashMessageHelper->info($this->translator->trans('forum.hold_back_for_moderation'));
            }
            $this->routeHelper->goAndExit($this->forumTransactions->url($region['id'], $ambassadorForum));
        }

        return $form->createView();
    }

    private function forum(Request $request, Response $response, $region, $ambassadorForum): void
    {
        $sub = $request->query->get('sub');
        $trans = $this->translator->trans(($ambassadorForum) ? 'terminology.ambassador_forum' : 'terminology.forum');
        $params = $this->regionViewData($region, $sub);
        $this->pageHelper->addBread($trans, $this->forumTransactions->url($region['id'], $ambassadorForum));
        $this->pageHelper->addTitle($trans);
        /* $viewdata['sub'] = $sub;

        if ($threadId = $request->query->getInt('tid')) {
            $thread = $this->forumGateway->getThreadInfo($threadId);
            if (empty($thread)) {
                $this->flashMessageHelper->error($this->translator->trans('forum.thread.not_found'));
                $this->routeHelper->goAndExit('/?page=bezirk&sub=forum&bid=' . $region['id']);
            }
            $this->pageHelper->addTitle($thread['title']);
            $viewdata['threadId'] = $threadId; // this triggers the rendering of the vue component `Thread`
        } elseif ($request->query->has('newthread')) {
            $this->pageHelper->addTitle($this->translator->trans('forum.new_thread'));
            $postActiveWithoutModeration = $this->forumPermissions->mayStartUnmoderatedThread($region, $ambassadorForum);
            $viewdata['newThreadForm'] = $this->handleNewThreadForm($request, $region, $ambassadorForum, $postActiveWithoutModeration);
            $viewdata['postActiveWithoutModeration'] = $postActiveWithoutModeration;
        } else {
            $viewdata['threads'] = []; // this triggers the rendering of the vue component `ThreadList`
        } */

        $this->pageHelper->addContent($this->view->vueComponent('region-page', 'RegionPage', $params));
    }

    private function events(Request $request, Response $response, $region): void
    {
        $this->pageHelper->addBread($this->translator->trans('events.bread'), '/?page=bezirk&bid=' . $region['id'] . '&sub=events');
        $this->pageHelper->addTitle($this->translator->trans('events.bread'));
        $sub = $request->query->get('sub');
        $params = $this->regionViewData($region, $sub);

        $this->pageHelper->addContent($this->view->vueComponent('region-page', 'RegionPage', $params));
    }

    private function applications(Request $request, Response $response, $region): void
    {
        $this->pageHelper->addBread($this->translator->trans('group.applications'), '/?page=bezirk&bid=' . $region['id'] . '&sub=events');
        $this->pageHelper->addTitle($this->translator->trans('group.applications_for', ['%name%' => $region['name']]));
        $sub = $request->query->get('sub');
        $viewdata = $this->regionViewData($region, $sub);
        if ($this->mayAccessApplications($region['id'])) {
            $viewdata['applications'] = $this->gateway->listApplicants($region['id']);
        }
        $response->setContent($this->render('pages/Region/applications.twig', $viewdata));
    }

    private function members(Request $request, Response $response, array $region): void
    {
        $this->pageHelper->addBread($this->translator->trans('group.members'), '/?page=bezirk&bid=' . $region['id'] . '&sub=members');
        $this->pageHelper->addTitle($this->translator->trans('group.members'));
        $sub = $request->query->get('sub');
        $viewdata = $this->regionViewData($region, $sub);

        if ($region['type'] === UnitType::WORKING_GROUP) {
            $mayEditMembers = $this->workGroupPermission->mayEdit($region);
            $maySetAdminOrAmbassador = $mayEditMembers;
            $mayRemoveAdminOrAmbassador = $mayEditMembers;
        } else {
            $mayEditMembers = $this->regionPermissions->mayDeleteFoodsaverFromRegion((int)$region['id']);
            $maySetAdminOrAmbassador = $this->regionPermissions->maySetRegionAdmin();
            $mayRemoveAdminOrAmbassador = $this->regionPermissions->mayRemoveRegionAdmin();
        }
        $viewdata['mayEditMembers'] = $mayEditMembers;
        $viewdata['maySetAdminOrAmbassador'] = $maySetAdminOrAmbassador;
        $viewdata['mayRemoveAdminOrAmbassador'] = $mayRemoveAdminOrAmbassador;
        $viewdata['userId'] = $this->session->id();
        $response->setContent($this->render('pages/Region/members.twig', $viewdata));
    }

    private function statistic(Request $request, Response $response, array $region): void
    {
        $this->pageHelper->addBread(
            $this->translator->trans('terminology.statistic'),
            '/?page=bezirk&bid=' . $region['id'] . '&sub=statistic'
        );
        $this->pageHelper->addTitle($this->translator->trans('terminology.statistic'));
        $sub = $request->query->get('sub');
        $viewData = $this->regionViewData($region, $sub);

        $viewData['genderData']['district'] = $this->gateway->genderCountRegion((int)$region['id']);
        $viewData['genderData']['homeDistrict'] = $this->gateway->genderCountHomeRegion((int)$region['id']);
        $viewData['pickupData']['daily'] = 0;
        $viewData['pickupData']['weekly'] = 0;
        $viewData['pickupData']['monthly'] = 0;
        $viewData['pickupData']['yearly'] = 0;
        $viewData['ageBand']['district'] = $this->gateway->AgeBandDistrict((int)$region['id']);
        $viewData['ageBand']['homeDistrict'] = $this->gateway->AgeBandHomeDistrict((int)$region['id']);

        if ($region['type'] !== UnitType::COUNTRY || $this->regionPermissions->mayAccessStatisticCountry()) {
            $viewData['pickupData']['daily'] = $this->gateway->listRegionPickupsByDate((int)$region['id'], '%Y-%m-%d');
            $viewData['pickupData']['weekly'] = $this->gateway->listRegionPickupsByDate((int)$region['id'], '%Y/%v');
            $viewData['pickupData']['monthly'] = $this->gateway->listRegionPickupsByDate((int)$region['id'], '%Y-%m');
            $viewData['pickupData']['yearly'] = $this->gateway->listRegionPickupsByDate((int)$region['id'], '%Y');
        }
        $response->setContent($this->render('pages/Region/statistic.twig', $viewData));
    }

    private function polls(Request $request, Response $response, array $region): void
    {
        $this->pageHelper->addBread($this->translator->trans('terminology.polls'), '/?page=bezirk&bid=' . $region['id'] . '&sub=polls');
        $this->pageHelper->addTitle($this->translator->trans('terminology.polls'));
        $params = $this->regionViewData($region, $request->query->get('sub'));
        // $viewdata['polls'] = $this->votingGateway->listPolls($region['id']);
        // $viewdata['regionId'] = $region['id'];
        // $viewdata['mayCreatePoll'] = $this->votingPermissions->mayCreatePoll($region['id']);
        $this->pageHelper->addContent($this->view->vueComponent('region-page', 'RegionPage', $params));
    }

    private function options(Request $request, Response $response, array $region): void
    {
        $this->pageHelper->addBread($this->translator->trans('terminology.options'), '/?page=bezirk&bid=' . $region['id'] . '&sub=options');
        $this->pageHelper->addTitle($this->translator->trans('terminology.options'));
        $viewdata = $this->regionViewData($region, $request->query->get('sub'));
        $regionOptions = $this->gateway->getAllRegionOptions($region['id']);
        $viewdata['maySetRegionOptionsReportButtons'] = boolval($this->regionPermissions->maySetRegionOptionsReportButtons($region['id']));
        $viewdata['maySetRegionOptionsRegionPickupRule'] = boolval($this->regionPermissions->maySetRegionOptionsRegionPickupRule($region['id']));
        $viewdata['isReportButtonEnabled'] = boolval(array_key_exists(RegionOptionType::ENABLE_REPORT_BUTTON, $regionOptions) ? $regionOptions[RegionOptionType::ENABLE_REPORT_BUTTON] : 0);
        $viewdata['isMediationButtonEnabled'] = boolval(array_key_exists(RegionOptionType::ENABLE_MEDIATION_BUTTON, $regionOptions) ? $regionOptions[RegionOptionType::ENABLE_MEDIATION_BUTTON] : 0);
        $viewdata['isRegionPickupRuleActive'] = boolval(array_key_exists(RegionOptionType::REGION_PICKUP_RULE_ACTIVE, $regionOptions) ? $regionOptions[RegionOptionType::REGION_PICKUP_RULE_ACTIVE] : 0);
        $viewdata['regionPickupRuleTimespanDays'] = intval(array_key_exists(RegionOptionType::REGION_PICKUP_RULE_TIMESPAN_DAYS, $regionOptions) ? $regionOptions[RegionOptionType::REGION_PICKUP_RULE_TIMESPAN_DAYS] : 0);
        $viewdata['regionPickupRuleLimitNumber'] = intval(array_key_exists(RegionOptionType::REGION_PICKUP_RULE_LIMIT_NUMBER, $regionOptions) ? $regionOptions[RegionOptionType::REGION_PICKUP_RULE_LIMIT_NUMBER] : 0);
        $viewdata['regionPickupRuleLimitDayNumber'] = intval(array_key_exists(RegionOptionType::REGION_PICKUP_RULE_LIMIT_DAY_NUMBER, $regionOptions) ? $regionOptions[RegionOptionType::REGION_PICKUP_RULE_LIMIT_DAY_NUMBER] : 0);
        $viewdata['regionPickupRuleInactiveHours'] = intval(array_key_exists(RegionOptionType::REGION_PICKUP_RULE_INACTIVE_HOURS, $regionOptions) ? $regionOptions[RegionOptionType::REGION_PICKUP_RULE_INACTIVE_HOURS] : 0);
        $viewdata['regionPickupRuleActiveStoreList'] = $this->storeGateway->listRegionStoresActivePickupRule($region['id']);

        $response->setContent($this->render('pages/Region/options.twig', $viewdata));
    }

    private function pin(Request $request, Response $response, array $region): void
    {
        $this->pageHelper->addBread($this->translator->trans('terminology.pin'), '/?page=bezirk&bid=' . $region['id'] . '&sub=pin');
        $this->pageHelper->addTitle($this->translator->trans('terminology.pin'));
        $viewdata = $this->regionViewData($region, $request->query->get('sub'));
        $result = $this->gateway->getRegionPin($region['id']);
        $viewdata['lat'] = $result['lat'] ?? MapConstants::CENTER_GERMANY_LAT;
        $viewdata['lon'] = $result['lon'] ?? MapConstants::CENTER_GERMANY_LON;
        $viewdata['desc'] = $result['desc'] ?? null;
        $viewdata['status'] = $result['status'] ?? null;
        $response->setContent($this->render('pages/Region/pin.twig', $viewdata));
    }
}
