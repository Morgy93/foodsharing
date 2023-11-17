<?php

namespace Foodsharing\Modules\Report;

use Foodsharing\Lib\Xhr\XhrDialog;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Report\ReportType;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Permissions\ReportPermissions;
use Foodsharing\Utility\Sanitizer;
use Foodsharing\Utility\TimeHelper;

class ReportXhr extends Control
{
    private $foodsaver;
    private $reportGateway;
    private $foodsaverGateway;
    private $sanitizerService;
    private $timeHelper;
    private $reportPermissions;

    public function __construct(
        ReportGateway $reportGateway,
        ReportView $view,
        FoodsaverGateway $foodsaverGateway,
        Sanitizer $sanitizerService,
        TimeHelper $timeHelper,
        ReportPermissions $reportPermissions,
    ) {
        $this->view = $view;
        $this->reportGateway = $reportGateway;
        $this->foodsaverGateway = $foodsaverGateway;
        $this->sanitizerService = $sanitizerService;
        $this->timeHelper = $timeHelper;
        $this->reportPermissions = $reportPermissions;

        parent::__construct();

        if (isset($_GET['fsid'])) {
            $this->foodsaver = $this->foodsaverGateway->getFoodsaver($_GET['fsid']);
            $this->view->setFoodsaver($this->foodsaver);
        }
    }

    public function loadReport(): ?array
    {
        if ($this->reportPermissions->mayHandleReports() && $report = $this->reportGateway->getReport($_GET['id'], ReportType::GOALS_REPORT)) {
            $reason = explode('=>', $report['tvalue']);

            $dialog = new XhrDialog();
            $dialog->setTitle($this->translator->trans('profile.report.xhr.reporting') . ' ' . $report['fs_name'] . ' ' . $report['fs_nachname']);

            $content = $this->v_utils->v_input_wrapper($this->translator->trans('profile.report.xhr.reportID'), $report['id']);
            $content .= $this->v_utils->v_input_wrapper($this->translator->trans('reports.time'), $this->timeHelper->niceDate($report['time_ts']));

            if (isset($report['betrieb'])) {
                $content .= $this->v_utils->v_input_wrapper($this->translator->trans('reports.store'), '<a href="/?page=fsbetrieb&id=' . $report['betrieb']['id'] . '">' . htmlspecialchars($report['betrieb']['name']) . '</a>');
            }

            if (\is_array($reason)) {
                $out = '<ul>';
                foreach ($reason as $r) {
                    $out .= '<li>' . htmlspecialchars(trim($r)) . '</li>';
                }
                $out .= '</ul>';

                $content .= $this->v_utils->v_input_wrapper($this->translator->trans('reports.reason'), $out);
            }

            if (!empty($report['msg'])) {
                $content .= $this->v_utils->v_input_wrapper($this->translator->trans('basket.description'), $this->sanitizerService->plainToHtml($report['msg']));
            }

            $content .= $this->v_utils->v_input_wrapper($this->translator->trans('profile.report.xhr.reportee'), '<a href="/profile/' . (int)$report['rp_id'] . '">' . htmlspecialchars($report['rp_name'] . ' ' . $report['rp_nachname']) . '</a>');
            $dialog->addContent($content);
            $dialog->addOpt('width', '$(window).width()*0.9');

            $dialog->addButton($this->translator->trans('profile.report.xhr.allofthem') . ' ' . $report['fs_name'], 'goTo(\'/?page=report&sub=foodsaver&id=' . $report['fs_id'] . '\');');

            $dialog->addButton($this->translator->trans('button.delete'), 'if(confirm("' . $this->translator->trans('profile.report.xhr.plsconfirm') . '")){ajreq(\'delReport\',{id:' . $report['id'] . '});$(\'#' . $dialog->getId() . '\').dialog(\'close\');}');

            return $dialog->xhrout();
        }

        return null;
    }

    public function delReport(): ?array
    {
        if ($this->reportPermissions->mayHandleReports()) {
            $this->reportGateway->delReport($_GET['id']);
            $this->flashMessageHelper->success($this->translator->trans('profile.report.xhr.deleted'));

            return [
                'status' => 1,
                'script' => 'reload();'
            ];
        }

        return null;
    }
}
