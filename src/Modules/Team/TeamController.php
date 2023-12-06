<?php

namespace Foodsharing\Modules\Team;

use Foodsharing\Lib\FoodsharingController;
use Foodsharing\Modules\Content\ContentGateway;
use Foodsharing\Modules\Core\DBConstants\Content\ContentId;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TeamController extends FoodsharingController
{
    public function __construct(
        private readonly TeamGateway $gateway,
        private readonly TeamView $view,
        private readonly ContentGateway $contentGateway,
    ) {
        parent::__construct();
    }

    // displays the board
    #[Route('/team', name: 'team')]
    public function index(): Response
    {
        $this->addCommonBread();

        // Type a, display "Vorstand" and "Aktive"
        $this->pageHelper->addContent("<div id='vorstand'>");
        $this->displayTeamContent(RegionIDs::TEAM_BOARD_MEMBER, ContentId::TEAM_HEADER_PAGE_39);
        $this->pageHelper->addContent("</div><div id='aktive'>");
        $this->displayTeamContent(RegionIDs::TEAM_ADMINISTRATION_MEMBER, ContentId::TEAM_ACTIVE_PAGE_53);
        $this->pageHelper->addContent('</div>');

        return $this->renderGlobal();
    }

    // displays a specific user
    #[Route('/team/{id}', name: 'team_id', requirements: ['id' => '\d+'])]
    public function byId(int $id): Response
    {
        $this->addCommonBread();

        if ($user = $this->gateway->getUser($id)) {
            $this->pageHelper->addTitle($user['name']);
            $this->pageHelper->addBread($user['name']);
            $this->pageHelper->addContent($this->view->user($user));

            if ($user['contact_public']) {
                $this->pageHelper->addContent($this->view->contactForm($user));
            }

            return $this->renderGlobal();
        } else {
            return $this->redirectToRoute('team');
        }
    }

    // /team/ehemalige - displays former active members
    #[Route('/team/{type}', name: 'team_type')]
    public function byType(string $type): Response
    {
        $this->addCommonBread();

        if ($type == 'ehemalige') {
            // Type b, display "Ehemalige"
            $this->pageHelper->addBread($this->translator->trans('team.former'), '/team/ehemalige');
            $this->pageHelper->addTitle($this->translator->trans('team.former'));
            $this->displayTeamContent(RegionIDs::TEAM_ALUMNI_MEMBER, ContentId::TEAM_FORMER_ACTIVE_PAGE_54);
        } else {
            $this->pageHelper->addContent($this->translator->trans('team.not-found'));
        }

        return $this->renderGlobal();
    }

    private function displayTeamContent($regionId, $contentId): void
    {
        if ($team = $this->gateway->getTeam($regionId)) {
            shuffle($team);
            $this->pageHelper->addContent($this->view->teamList($team, $this->contentGateway->get($contentId)));
        }
    }

    private function addCommonBread(): void
    {
        $this->pageHelper->addBread($this->translator->trans('team.current'), '/team');
        $this->pageHelper->addTitle($this->translator->trans('team.current'));
    }
}
