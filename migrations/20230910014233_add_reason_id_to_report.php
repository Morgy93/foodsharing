<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddReasonIdToReport extends AbstractMigration
{
    public function change(): void
    {
        $this->table('fs_report')
            ->addColumn(
                'rp_reason_id',
                'integer',
                [
                    'null' => false,
                    'default' => 1,
                    'limit' => 6,
                    'signed' => false,
                    'comment' => 'Report Reason ID'
                ]
            )
            ->update();
        $this->table('fs_report')
            ->addColumn(
                'report_type',
                'integer',
                [
                    'null' => false,
                    'default' => 1,
                    'limit' => 1,
                    'signed' => false,
                    'comment' => 'Report Type (1=Goals-Report, etc.)'
                ]
            )
            ->update();
    }
}
