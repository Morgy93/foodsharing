<?php

namespace Foodsharing\Modules\Report;

use Foodsharing\Modules\Core\View;

class ReportView extends View
{
    public function listReportsTiny($reports): string
    {
        $out = '<ul class="linklist">';

        foreach ($reports as $r) {
            $name = '';
            if (!empty($r['rp_name'])) {
                $name = ' von ' . $r['rp_name'] . ' ' . $r['rp_nachname'] . '';
            }
            $out .= '<li><a href="#" onclick="ajreq(\'loadReport\',{id:' . (int)$r['id'] . '})">' . date('d.m.Y', $r['time_ts']) . $name . '</a></li>';
        }

        $out .= '</ul>';

        return $this->v_utils->v_field($out, $this->translator->trans('reports.all_reports'));
    }
}
