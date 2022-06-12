<?php

namespace Foodsharing\Modules\PassportGenerator;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Utility\IdentificationHelper;

final class PassportGeneratorControl extends Control
{
	private $regionId;
	private $region;
	private RegionGateway $regionGateway;
	private PassportGeneratorGateway $passportGeneratorGateway;
	private IdentificationHelper $identificationHelper;
	private PassportGeneratorTransaction $passportGeneratorTransaction;

	public function __construct(
		PassportGeneratorView $view,
		RegionGateway $regionGateway,
		PassportGeneratorGateway $passportGateway,
		IdentificationHelper $identificationHelper,
		PassportGeneratorTransaction $passportGeneratorTransaction
	) {
		$this->view = $view;
		$this->regionGateway = $regionGateway;
		$this->passportGeneratorGateway = $passportGateway;
		$this->identificationHelper = $identificationHelper;
		$this->passportGeneratorTransaction = $passportGeneratorTransaction;

		parent::__construct();

		$this->regionId = false;
		if (($this->regionId = $this->identificationHelper->getGetId('bid')) === false) {
			$this->regionId = $this->session->getCurrentRegionId();
		}

		if ($this->session->isAmbassadorForRegion([$this->regionId], false, true) || $this->session->may('orga')) {
			$this->region = false;
			if ($region = $this->regionGateway->getRegion($this->regionId)) {
				$this->region = $region;
			}
		} else {
			$this->routeHelper->go('/?page=dashboard');
		}
	}

	public function index(): void
	{
		$this->pageHelper->addBread($this->region['name'], '/?page=bezirk&bid=' . $this->regionId);
		$this->pageHelper->addBread($this->translator->trans('pass.bread'));

		$this->pageHelper->addTitle($this->region['name']);
		$this->pageHelper->addTitle($this->translator->trans('pass.bread'));

		if (isset($_POST['passes']) && !empty($_POST['passes'])) {
			$this->passportGeneratorTransaction->generate($_POST['passes'], true, $this->region);
		}

		if ($regions = $this->passportGeneratorGateway->getPassFoodsaver($this->regionId)) {
			$this->pageHelper->addHidden('
			<div id="verifyconfirm-dialog" title="' . $this->translator->trans('pass.verify.confirm') . '">'
				. $this->v_utils->v_info(
					'<p>' . $this->translator->trans('pass.verify.text') . '</p>',
					$this->translator->trans('pass.verify.confirm')
				) .
			'</div>');

			$this->pageHelper->addHidden('
			<div id="unverifyconfirm-dialog" title="' . $this->translator->trans('pass.verify.failed') . '">'
				. $this->v_utils->v_info(
					'<p>' . $this->translator->trans('pass.verify.checkPickups') . '</p>',
					$this->translator->trans('pass.verify.hasPickup')
				) .
			'</div>');

			$this->pageHelper->addContent('<form id="generate" method="post">');
			foreach ($regions as $region) {
				$this->pageHelper->addContent($this->view->passTable($region));
			}
			$this->pageHelper->addContent('</form>');
			$this->pageHelper->addContent($this->view->menubar(), CNT_RIGHT);
			$this->pageHelper->addContent($this->view->start(), CNT_RIGHT);
			$this->pageHelper->addContent($this->view->tips(), CNT_RIGHT);
		}

		if (isset($_GET['dl1'])) {
			$this->download1();
		}
		if (isset($_GET['dl2'])) {
			$this->download2();
		}
	}

	private function download1(): void
	{
		$this->pageHelper->addJs('
			setTimeout(function(){goTo("/?page=passgen&bid=' . $this->regionId . '&dl2")},100);
		');
	}

	private function download2(): void
	{
		$bez = strtolower($this->region['name']);

		$bez = str_replace(['ä', 'ö', 'ü', 'ß'], ['ae', 'oe', 'ue', 'ss'], $bez);
		$bez = preg_replace('/[^a-zA-Z]/', '', $bez);
		$file = 'data/pass/foodsaver_pass_' . $bez . '.pdf';

		$filename = basename($file);
		$size = filesize($file);
		header('Content-Type: application/pdf');
		header('Content-Disposition: attachment; filename=' . $filename . '');
		header("Content-Length: $size");
		readfile($file);

		exit();
	}
}
