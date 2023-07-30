<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddInfoTypeToFoodsaverHasBezirk extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $this->table('fs_foodsaver_has_bezirk')
            ->addColumn('notify_by_email_about_new_threads', 'boolean', [
                'null' => false,
                'default' => 1,
                'limit' => 1,
                'signed' => false,
                'comment' => 'Emails from new forum threads in regions and working groups can be disabled.',
            ])->save();
    }
}
