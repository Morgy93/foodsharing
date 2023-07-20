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
            'estimated_store_count',
            'integer',
            [
                'null' => false,
                'default' => 0,
                'signed' => false,
                'limit' => 6
            ]
        )->addColumn(
            'headquarters_country',
            'string',
            [
                'null' => true,
                'limit' => 50,
            ]
        )->dropForeignKey('forum_thread')
        ->addForeignKey('forum_thread', 'fs_theme', 'id', ['delete' => 'SET NULL'])

        // Add index for a better filter of store chains on server side
        // https://mariadb.com/kb/en/full-text-index-overview/
        ->addIndex('name', ['type' => 'fulltext'])
        ->addIndex('notes', ['type' => 'fulltext'])
        ->addIndex('common_store_information', ['type' => 'fulltext'])
        ->addIndex('headquarters_city', ['type' => 'fulltext'])
        ->save();
    }
}
