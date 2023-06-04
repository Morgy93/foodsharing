<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ChainAddStoreCountFixFk extends AbstractMigration
{
    public function change(): void
    {
        $this->table(
            'fs_chain',
            [
                'id' => false,
                'primary_key' => ['id']
            ]
        )->addColumn(
            'estimatedStoreCount',
            'integer',
            [
                'null' => false,
                'default' => 0,
                'signed' => false,
                'limit' => 6
            ]
        )->dropForeignKey('forum_thread')
        ->addForeignKey('forum_thread', 'fs_theme', 'id', ['delete' => 'SET NULL'])
        ->save();
    }
}
