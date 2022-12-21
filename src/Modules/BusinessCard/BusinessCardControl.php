<?php

namespace Foodsharing\Modules\BusinessCard;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use setasign\Fpdi\Tcpdf\Fpdi;
use Symfony\Component\HttpKernel\KernelInterface;

class BusinessCardControl extends Control
{
    private string $projectDir;
    private const MAX_CHAR_PER_LINE = 45;

    public function __construct(
        BusinessCardView $view,
        private readonly BusinessCardGateway $gateway,
        KernelInterface $kernel
    ) {
        $this->view = $view;
        $this->projectDir = $kernel->getProjectDir();

        parent::__construct();
    }

    public function index(): void
    {
        if (!$this->session->mayRole()) {
            $this->routeHelper->goLogin();
        }

        $this->pageHelper->addBread($this->translator->trans('bcard.title'));

        $this->pageHelper->addContent($this->view->top(), CNT_TOP);

        if ($data = $this->gateway->getMyData($this->session->id(), $this->session->mayRole(Role::STORE_MANAGER))) {
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

            foreach ($data['bot'] as $b) {
                $choices[] = [
                    'id' => 'bot:' . $b['id'],
                    'name' => $this->translator->trans('bcard.for', [
                        '{role}' => $this->translator->trans('terminology.ambassador.d'),
                        '{region}' => $b['name'],
                    ]),
                ];
            }
            foreach ($data['sm'] as $b) {
                $choices[] = [
                    'id' => 'sm:' . $b['id'],
                    'name' => $this->translator->trans('bcard.for', [
                        '{role}' => $this->translator->trans('terminology.storemanager.d'),
                        '{region}' => $b['name'],
                    ]),
                ];
            }
            foreach ($data['fs'] as $b) {
                $choices[] = [
                    'id' => 'fs:' . $b['id'],
                    'name' => $this->translator->trans('bcard.for', [
                        '{role}' => $this->translator->trans('terminology.foodsaver.d'),
                        '{region}' => $b['name'],
                    ]),
                ];
            }

            $this->pageHelper->addContent($this->view->optionForm($choices));
        }
    }

    public function makeCard()
    {
        $data = $this->gateway->getMyData($this->session->id(), $this->session->mayRole(Role::STORE_MANAGER));
        $opt = $this->getRequest('opt');
        if (!$data || !$opt) {
            return;
        } else {
            $opt = explode(':', $opt); // role:region
        }

        if (count($opt) != 2 || (int)$opt[1] < 0) {
            return;
        }

        $regionId = (int)$opt[1];
        $role = $opt[0];
        $mailbox = false;

        if (isset($data[$role]) && $data[$role] != false) {
            foreach ($data[$role] as $d) {
                if ($d['id'] == $regionId) {
                    $mailbox = $d;
                }
            }
        } else {
            return;
        }

        if (!$mailbox) {
            return;
        }

        if (isset($mailbox['email'])) {
            $data['email'] = $mailbox['email'];
        }
        $data['subtitle'] = $this->displayedRole($role, $data['geschlecht'], $mailbox['name']);

        if (mb_strlen($data['anschrift']) > self::MAX_CHAR_PER_LINE) {
            $street_number_pos = $this->index_of_first_number($data['anschrift']);
            $length_street_number = mb_strlen($data['anschrift']) - $street_number_pos;
            $data['anschrift'] = mb_substr($data['anschrift'], 0, self::MAX_CHAR_PER_LINE - $length_street_number - 4) . '... ' .
                mb_substr($data['anschrift'], $street_number_pos, $length_street_number);
        }

        if (mb_strlen($data['plz'] . ' ' . $data['stadt']) >= self::MAX_CHAR_PER_LINE) {
            $data['stadt'] = mb_substr($data['stadt'], 0, self::MAX_CHAR_PER_LINE - strlen($data['plz']) - 4) . '...';
        }

        $this->generatePdf($data, $role);
    }

    private function displayedRole(string $role, int $gender, string $regionName): string
    {
        $modifier = 'dmfd'[$gender]; // 0=d 1=m 2=f 3=d
        switch ($role) {
            case 'sm':
                $roleName = $this->translator->trans('terminology.storemanager.' . $modifier);
                break;
            case 'bot':
                $roleName = $this->translator->trans('terminology.ambassador.' . $modifier);
                break;
            case 'fs':
            default:
                $roleName = $this->translator->trans('terminology.foodsaver.' . $modifier);
                break;
        }

        return $this->translator->trans('bcard.for', ['{role}' => $roleName, '{region}' => $regionName]);
    }

    private function generatePdf(array $data, string $role = 'fs'): void
    {
        $pdf = new Fpdi();
        $pdf->AddPage();
        $pdf->SetTextColor(0, 0, 0);
        $pdf->AddFont('Ubuntu-L', '', $this->projectDir . '/lib/font/ubuntul.php', true);
        $pdf->AddFont('AcmeFont Regular', '', $this->projectDir . '/lib/font/acmefont.php', true);

        $x = 0.0;
        $y = 0.0;

        for ($i = 0; $i < 8; ++$i) {
            $pdf->Image($this->projectDir . '/img/fsvisite.png', 10 + $x, 10 + $y, 91, 61);

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
            $pdf->Text(52.3 + $x, 44.8 + $y, $data['anschrift']);
            $pdf->Text(52.3 + $x, 47.8 + $y, $data['plz'] . ' ' . $data['stadt']);
            $tel = $data['handy'];
            if (empty($tel)) {
                $tel = $data['telefon'];
            }

            $pdf->Text(52.3 + $x, 51.8 + $y, $tel);
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
