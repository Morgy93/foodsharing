<?php

namespace Foodsharing\Modules\DropOffPoint;

use Foodsharing\Lib\Xhr\XhrDialog;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Utility\Sanitizer;

/**
 * Can be instantiated via reflection by the @see Routing.php.
 */
class DropOffPointXhr extends Control
{
	private DropOffPointGateway $dropOffPointGateway;
	private Sanitizer $sanitizerService;

	public function __construct(
		DropOffPointGateway $DropOffPointGateway,
		Sanitizer $sanitizerService
	) {
		$this->dropOffPointGateway = $DropOffPointGateway;
		$this->sanitizerService = $sanitizerService;

		parent::__construct();

		if (!$this->session->may('fs')) {
			exit();
		}
	}

	public function bubble(): array
	{
		$dropOffPointId = (int)$_GET['id'];

		if ($dropOffPoint = $this->dropOffPointGateway->getDropOffPoint($dropOffPointId)) {
			$xhrDialog = new XhrDialog();

			$xhrDialog->setTitle($dropOffPoint['name']);
			$xhrDialog->addContent($dropOffPoint['description']);
			$xhrDialog->addButton($this->translator->trans('drop_off_point.go'),
				'goTo(\'/drop-off-point/' . $dropOffPointId . '\');'
			);

			return $xhrDialog->xhrout();
		}

		return [
			'status' => 1,
			'script' => 'pulseError("' . $this->translator->trans('store.error') . '");',
		];
	}
}
