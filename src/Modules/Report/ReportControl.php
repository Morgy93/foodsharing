<?php

namespace Foodsharing\Modules\Report;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Permissions\ReportPermissions;
use Foodsharing\Utility\ImageHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ReportControl extends Control
{
	private $reportGateway;
	private $imageService;
	private $reportPermissions;

	public function __construct(
		ReportGateway $reportGateway,
		ReportView $view,
		ImageHelper $imageService,
		ReportPermissions $reportPermissions)
	{
		$this->reportGateway = $reportGateway;
		$this->view = $view;
		$this->imageService = $imageService;
		$this->reportPermissions = $reportPermissions;

		parent::__construct();

		if (!$this->session->mayRole()) {
			$this->routeHelper->goLogin();
		}
	}

	// Request is needed here, even if not used inside the method.
	public function index(Request $request, Response $response): void
	{
		if (isset($_GET['bid'])) {
			// $this->pageHelper->addContent($this->v_utils->v_info('<b>Während einer langen Probephase konnten Probleme dieser Funktion leider nicht entdeckt werden. Diese Funktion wird deshalb auf Wunsch der AG Meldegruppe ( <a href="mailto:meldungen@foodsharing.network">meldungen@foodsharing.network</a> ) vorübergehend deaktiviert.<br><br><br>Um sie nach einer Ausarbeitung durch die IT wieder zu aktivieren, benötigen wir die Unterstützung weiterer ProgrammiererInnen aus Deinem Bezirk:<br><br><a href="https://devdocs.foodsharing.network/it-tasks.html">https://devdocs.foodsharing.network/it-tasks.html</a> oder <a href="mailto:it@foodsharing.network">it@foodsharing.network</a></b>'));
			$this->byRegion($_GET['bid'], $response);
		//return;
		} else {
			if (!isset($_GET['sub'])) {
				$this->routeHelper->go('/?page=report&sub=uncom');
			}
			if ($this->reportPermissions->mayHandleReports()) {
				$this->pageHelper->addBread($this->translator->trans('menu.reports'), '/?page=report');
			} else {
				$this->routeHelper->go('/?page=dashboard');
			}
		}
	}

	private function byRegion($regionId, $response)
	{
		$response->setContent($this->render('pages/Report/by-region.twig',
			['bid' => $regionId]
		));
	}

	public function uncom(): void
	{
		if ($this->reportPermissions->mayHandleReports()) {
			$this->pageHelper->addContent($this->view->statsMenu($this->reportGateway->getReportStats()), CNT_LEFT);

			if ($reports = $this->reportGateway->getReports(0)) {
				$this->pageHelper->addContent($this->view->listReports($reports));
			}
			$this->pageHelper->addContent($this->view->topbar($this->translator->trans('profile.report.control.newreports'), \count($reports) . ' ' . $this->translator->trans('profile.report.control.total'), '<img src="/img/shit.png" />'), CNT_TOP);
		}
	}

	public function com(): void
	{
		if ($this->reportPermissions->mayHandleReports()) {
			$this->pageHelper->addContent($this->view->statsMenu($this->reportGateway->getReportStats()), CNT_LEFT);

			if ($reports = $this->reportGateway->getReports(1)) {
				$this->pageHelper->addContent($this->view->listReports($reports));
			}
			$this->pageHelper->addContent($this->view->topbar($this->translator->trans('profile.report.control.delivered'), \count($reports) . ' ' . $this->translator->trans('profile.report.control.total'), '<img src="/img/shit.png" />'), CNT_TOP);
		}
	}

	public function foodsaver(): void
	{
		if ($this->reportPermissions->mayHandleReports()) {
			if ($foodsaver = $this->reportGateway->getReportedSaver($_GET['id'])) {
				$this->pageHelper->addBread(
					$this->translator->trans('menu.reports'),
					'/?page=report&sub=foodsaver&id=' . (int)$foodsaver['id']
				);
				$this->pageHelper->addJs(
					'
						$(".welcome_profile_image").css("cursor","pointer");
						$(".welcome_profile_image").on("click", function(){
							$(".user_display_name a").trigger("click");
						});
				'
				);
				$this->pageHelper->addContent(
					$this->view->topbar(
						$this->translator->trans('profile.report.control.from') . ' <a href="/profile/' . (int)$foodsaver['id'] . '">' . $foodsaver['name'] . ' ' . $foodsaver['nachname'] . '</a>',
						\count($foodsaver['reports']) . ' ' . $this->translator->trans('profile.report.control.tot'),
						$this->imageService->avatar($foodsaver, 50)
					),
					CNT_TOP
				);
				$this->pageHelper->addContent(
					$this->v_utils->v_field(
						$this->wallposts('fsreport', (int)$_GET['id']),
						$this->translator->trans('profile.report.control.notes')
					)
				);
				$this->pageHelper->addContent(
					$this->view->listReportsTiny($foodsaver['reports']),
					CNT_RIGHT
				);
			}
		} else {
			$this->routeHelper->go('/?page=dashboard');
		}
	}
}
