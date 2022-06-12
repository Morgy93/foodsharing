<?php

namespace Foodsharing\Modules\Region;

use Foodsharing\Lib\Xhr\XhrDialog;
use Foodsharing\Modules\Core\Control;

final class RegionXhr extends Control
{
	private RegionGateway $regionGateway;
	private \Twig\Environment $twig;

	public function __construct(
		RegionGateway $regionGateway,
		\Twig\Environment $twig
	) {
		$this->regionGateway = $regionGateway;
		$this->twig = $twig;

		parent::__construct();
	}

	public function bubble(): array
	{
		$region_id = $_GET['id'];
		$pin = $this->regionGateway->getRegionPin($region_id);
		if (!$pin) {
			return [
				'status' => 1,
				'script' => 'pulseError("' . $this->translator->trans('pin.error') . '");',
			];
		}
		$region = $this->regionGateway->getRegionDetails($region_id);

		$dia = new XhrDialog();

		$dia->setTitle($this->translator->trans('terminology.community', ['{name}' => $region['name']]));
		$dia->addContent($this->twig->render('partials/vue-wrapper.twig', [
			'id' => 'community-bubble',
			'component' => 'CommunityBubble',
			'props' => [
				'regionId' => $region['id'],
				'name' => $region['name'],
				'desc' => $pin['desc']
			],
			'initialData' => [],
		]));

		$dia->addOpt('modal', 'false', false);
		$dia->addOpt('resizeable', 'false', false);
		$dia->noOverflow();

		return $dia->xhrout();
	}
}
