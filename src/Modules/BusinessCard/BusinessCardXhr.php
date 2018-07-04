<?php

namespace Foodsharing\Modules\BusinessCard;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\Model;
use setasign\Fpdi;

class BusinessCardXhr extends Control
{
	private $gateway;

	public function __construct(BusinessCardGateway $gateway, Model $model)
	{
		$this->gateway = $gateway;
		$this->model = $model;

		parent::__construct();
	}

	public function makeCard()
	{
		if (($data = $this->gateway->getMyData($this->session->id(), $this->session->may('bieb'))) && ($opt = $this->getRequest('opt'))) {
			$opt = explode(':', $opt);
			if (count($opt) == 2 && (int)$opt[1] > 0) {
				$id = (int)$opt[1];
				$type = $opt[0];
				$mailbox = false;
				if (isset($data[$type]) && $data[$type] != false) {
					foreach ($data[$type] as $d) {
						if ($d['id'] == $id) {
							$mailbox = $d;
						}
					}
				} else {
					return false;
				}
				if ($mailbox !== false) {
					if ($type == 'fs') {
						if ($data['geschlecht'] == 2) {
							$data['subtitle'] = $this->func->sv('fs_for_w', $mailbox['name']);
						} else {
							$data['subtitle'] = $this->func->sv('fs_for', $mailbox['name']);
						}
					} elseif ($type == 'sm') {
						if ($data['geschlecht'] == 2) {
							$data['subtitle'] = $this->func->sv('sm_for_w', $mailbox['name']);
						} else {
							$data['subtitle'] = $this->func->sv('sm_for', $mailbox['name']);
						}
					} elseif ($type == 'bot') {
						if ($data['geschlecht'] == 2) {
							$data['subtitle'] = $this->func->sv('bot_for_w', $mailbox['name']);
						} else {
							$data['subtitle'] = $this->func->sv('bot_for', $mailbox['name']);
						}
						$data['email'] = $mailbox['email'];
					} else {
						return false;
					}
				}

				return $this->generatePdf($data, $type);
			}
		}
	}

	private function generatePdf($data, $type = 'fs')
	{
		$pdf = new Fpdi\Fpdi();
		$pdf->AddPage();
		$pdf->SetTextColor(0, 0, 0);
		$pdf->AddFont('Ubuntu-L', '', 'Ubuntu-L.php');
		$pdf->AddFont('AcmeFont Regular', '', 'acmefontregular.php');

		$x = 0;
		$y = 0;

		for ($i = 0; $i < 8; ++$i) {
			$pdf->Image('img/fsvisite.png', 10 + $x, 10 + $y, 91, 61);

			$pdf->SetTextColor(85, 60, 36);

			if (strlen($data['name'] . ' ' . $data['nachname']) >= 33) {
				$pdf->SetFont('Ubuntu-L', '', 5);
				$pdf->Text(49.4 + $x, 33 + $y, utf8_decode($data['name'] . ' ' . $data['nachname']));
			} elseif (strlen($data['name'] . ' ' . $data['nachname']) >= 22) {
				$pdf->SetFont('Ubuntu-L', '', 7);
				$pdf->Text(49.4 + $x, 33 + $y, utf8_decode($data['name'] . ' ' . $data['nachname']));
			} else {
				$pdf->SetFont('Ubuntu-L', '', 10);
				$pdf->Text(49.4 + $x, 33 + $y, utf8_decode($data['name'] . ' ' . $data['nachname']));
			}

			$pdf->SetFont('Ubuntu-L', '', 7);
			if (strlen($data['anschrift'] . ', ' . $data['plz'] . ' ' . $data['stadt']) > 32) {
				$pdf->SetFont('Ubuntu-L', '', 6);
			}

			$pdf->Text(49.4 + $x, 37 + $y, utf8_decode($data['subtitle']));

			$pdf->SetTextColor(0, 0, 0);
			$pdf->Text(53.4 + $x, 48.4 + $y, utf8_decode($data['anschrift'] . ', ' . $data['plz'] . ' ' . $data['stadt']));

			$tel = $data['handy'];
			if (empty($tel)) {
				$tel = $data['telefon'];
			}

			$pdf->Text(53.4 + $x, 53.6 + $y, utf8_decode($tel));
			$pdf->Text(53.4 + $x, 58.3 + $y, utf8_decode($data['email']));
			$pdf->Text(53.4 + $x, 63.2 + $y, utf8_decode(BASE_URL));
			if ($x == 0) {
				$x += 91;
			} else {
				$y += 61;
				$x = 0;
			}
		}

		$file = 'data/visite/' . $data['id'] . '_' . $type . '.pdf';
		@unlink($file);
		$pdf->Output('F', $file);

		return array(
			'status' => 1,
			'script' => '
				pulseInfo(\'' . $this->func->jsSafe($this->func->s('generation_success')) . '\');
				u_download("' . $type . ':' . $data['id'] . '");'
		);
	}
}
