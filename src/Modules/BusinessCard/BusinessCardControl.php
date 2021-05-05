<?php

namespace Foodsharing\Modules\BusinessCard;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use JeroenDesloovere\VCard\VCard;
use setasign\Fpdi\Tcpdf\Fpdi;

class BusinessCardControl extends Control
{
	private BusinessCardGateway $gateway;
	private const MAX_CHAR_PER_LINE = 45;

	public function __construct(BusinessCardView $view, BusinessCardGateway $gateway)
	{
		$this->view = $view;
		$this->gateway = $gateway;

		parent::__construct();
	}

	public function index(): void
	{
		$this->pageHelper->addBread($this->translator->trans('bcard.title'));

		$this->pageHelper->addContent($this->view->top(), CNT_TOP);

		if ($data = $this->gateway->getFoodsaverData($this->session->id())) {
			if (mb_strlen($data['anschrift']) >= self::MAX_CHAR_PER_LINE || mb_strlen($data['plz'] . ' ' . $data['stadt']) >= self::MAX_CHAR_PER_LINE) {
				$this->flashMessageHelper->info($this->translator->trans('bcard.info.address_shortened'));
			}
			if (strlen($data['telefon'] . $data['handy']) <= 3) {
				$this->flashMessageHelper->error($this->translator->trans('bcard.error.phone'));
				$this->routeHelper->go('/?page=settings');
			}
			if ($data['verified'] == 0) {
				$this->flashMessageHelper->error($this->translator->trans('bcard.error.verified'));
				$this->routeHelper->go('/?page=settings');
			}

			$choices = [];

			if ($this->session->may('bot')) {
				$ambassadorRegions = $this->gateway->getAmbassadorRegions($this->session->id());
				foreach ($ambassadorRegions as $b) {
					$choices[] = [
						'value' => Role::AMBASSADOR . ':' . $b['id'],
						'text' => $this->translator->trans('bcard.for', [
							'{role}' => $this->translator->trans('terminology.ambassador.d'),
							'{region}' => $b['name'],
						]),
					];
				}
			}

			if ($this->session->may('fs')) {
				$fsRegions = $this->gateway->getFoodsaverRegions($this->session->id());
				foreach ($fsRegions as $b) {
					$choices[] = [
						'value' => Role::FOODSAVER . ':' . $b['id'],
						'text' => $this->translator->trans('bcard.for', [
							'{role}' => $this->translator->trans('terminology.foodsaver.d'),
							'{region}' => $b['name'],
						]),
					];

					if ($this->session->may('bieb')) {
						$choices[] = [
							'value' => Role::STORE_MANAGER . ':' . $b['id'],
							'text' => $this->translator->trans('bcard.for', [
								'{role}' => $this->translator->trans('terminology.storemanager.d'),
								'{region}' => $b['name'],
							]),
						];
					}
				}
			}

			$this->pageHelper->addContent($this->view->optionForm($choices));
		}
	}

	public function makeCard()
	{
		$data = $this->gateway->getFoodsaverData($this->session->id());
		if (!$data) {
			return;
		}

		$opt = $this->getRequest('opt');
		if (!$data || !$opt) {
			return;
		}

		$opt = explode(':', $opt);
		if (count($opt) != 2 || (int)$opt[1] < 0) {
			return;
		}

		$regionId = (int)$opt[1];
		$role = (int)$opt[0];
		$mailbox = false;

		$regions = [];
		switch ($role) {
			case Role::FOODSAVER:
			case Role::STORE_MANAGER:
				$regions = $this->gateway->getFoodsaverRegions($this->session->id());
				break;
			case Role::AMBASSADOR:
				$regions = $this->gateway->getAmbassadorRegions($this->session->id());
				break;
		}

		foreach ($regions as $d) {
			if ($d['id'] == $regionId) {
				$mailbox = $d;
			}
		}

		if (!$mailbox) {
			return;
		}

		if (isset($mailbox['email'])) {
			$data['email'] = $this->gateway->getMailboxData($this->session->id());
		}

		$roleName = $this->translator->trans($this->translationHelper->getRoleName($role, $data['geschlecht']));
		$data['subtitle'] = $this->translator->trans('bcard.for', ['{role}' => $roleName, '{region}' => $mailbox['name']]);

		$includeAddress = boolval($this->getRequest('address'));
		$includePhone = boolval($this->getRequest('phone'));
		$createQRCode = boolval($this->getRequest('qr'));

		if (mb_strlen($data['anschrift']) > self::MAX_CHAR_PER_LINE) {
			$street_number_pos = $this->index_of_first_number($data['anschrift']);
			$length_street_number = mb_strlen($data['anschrift']) - $street_number_pos;
			$data['anschrift'] = mb_substr($data['anschrift'], 0, (self::MAX_CHAR_PER_LINE - $length_street_number - 4)) . '... ' .
				mb_substr($data['anschrift'], $street_number_pos, $length_street_number);
		}

		if (mb_strlen($data['plz'] . ' ' . $data['stadt']) >= self::MAX_CHAR_PER_LINE) {
			$data['stadt'] = mb_substr($data['stadt'], 0, (self::MAX_CHAR_PER_LINE - strlen($data['plz']) - 4)) . '...';
		}

		$this->generatePdf($data, $role, $includeAddress, $includePhone, $createQRCode);
	}

	private function generatePdf(array $data, string $role = 'fs', bool $includeAddress = true,
								 bool $includePhone = true, bool $createQRCode = false): void
	{
		$pdf = new Fpdi();
		$pdf->AddPage();
		$pdf->SetTextColor(0, 0, 0);
		$pdf->AddFont('Ubuntu-L', '', 'lib/font/ubuntul.php', true);
		$pdf->AddFont('AcmeFont Regular', '', 'lib/font/acmefont.php', true);

		$x = 0;
		$y = 0;

		for ($i = 0; $i < 8; ++$i) {
			$pdf->Image('img/fsvisite.png', 10 + $x, 10 + $y, 91, 61);

			$pdf->SetTextColor(85, 60, 36);

			if (strlen($data['name'] . ' ' . $data['nachname']) >= 33) {
				$pdf->SetFont('Ubuntu-L', '', 5);
				$pdf->Text(48.5 + $x, 29.5 + $y, $data['name'] . ' ' . $data['nachname']);
			} elseif (strlen($data['name'] . ' ' . $data['nachname']) >= 22) {
				$pdf->SetFont('Ubuntu-L', '', 7);
				$pdf->Text(48.5 + $x, 29.5 + $y, $data['name'] . ' ' . $data['nachname']);
			} else {
				$pdf->SetFont('Ubuntu-L', '', 10);
				$pdf->Text(48.5 + $x, 29.5 + $y, $data['name'] . ' ' . $data['nachname']);
			}

			$pdf->SetFont('Ubuntu-L', '', 7);
			if (strlen($data['anschrift'] . ', ' . $data['plz'] . ' ' . $data['stadt']) > 32) {
				$pdf->SetFont('Ubuntu-L', '', 6);
			}

			$pdf->SetXY(48.5 + $x, 35.2 + $y);
			$pdf->MultiCell(50, 12, $data['subtitle'], 0, 'L');

			$pdf->SetTextColor(0, 0, 0);
			if ($includeAddress) {
				$pdf->Text(52.3 + $x, 44.8 + $y, $data['anschrift']);
				$pdf->Text(52.3 + $x, 47.8 + $y, $data['plz'] . ' ' . $data['stadt']);
			}

			if ($includePhone) {
				$tel = $data['handy'];
				if (empty($tel)) {
					$tel = $data['telefon'];
				}
				$pdf->Text(52.3 + $x, 51.8 + $y, $tel);
			}

			$pdf->Text(52.3 + $x, 56.2 + $y, $data['email']);
			$pdf->Text(52.3 + $x, 61.6 + $y, BASE_URL);
			if ($x == 0) {
				$x += 91;
			} else {
				$y += 61;
				$x = 0;
			}
		}

		$pdf->Output('bcard-' . $role . '.pdf', 'D');
	}

	private function index_of_first_number($text)
	{
		preg_match('/\d/u', $text, $m, PREG_OFFSET_CAPTURE);
		if (sizeof($m)) {
			return mb_strlen(substr($text, 0, $m[0][1]));
		}

		// return position of the first number in the string
		return strlen($text);
	}
}
