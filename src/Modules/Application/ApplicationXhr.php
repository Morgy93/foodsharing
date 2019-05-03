<?php

namespace Foodsharing\Modules\Application;

use Foodsharing\Modules\Core\Control;

class ApplicationXhr extends Control
{
	private $gateway;

	public function __construct(ApplicationGateway $gateway, ApplicationView $view)
	{
		$this->gateway = $gateway;
		$this->view = $view;

		parent::__construct();
	}

	public function accept()
	{
		if ($this->session->isAdminFor($_GET['bid']) || $this->session->isOrgaTeam()) {
			$this->gateway->acceptApplication($_GET['bid'], $_GET['fid']);
			$this->flashMessageHelper->info('Bewerbung angenommen');

			return array(
					'status' => 1,
					'script' => 'goTo("/?page=bezirk&bid=' . (int)$_GET['bid'] . '");'
				);
		}
	}

	public function decline()
	{
		if ($this->session->isAdminFor($_GET['bid']) || $this->session->isOrgaTeam()) {
			$this->gateway->denyApplication($_GET['bid'], $_GET['fid']);

			$this->flashMessageHelper->info('Bewerbung abgelehnt');

			return array(
				'status' => 1,
				'script' => 'goTo("/?page=bezirk&bid=' . (int)$_GET['bid'] . '");'
			);
		}
	}
}
