<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddCoordinatorChatToStore extends AbstractMigration
{
    public function change(): void
    {
       $this->table('fs_betrieb')
            ->addColumn('coordinator_conversation_id', 'integer', [
                'null' => true,
                'default' => null,
                'limit' => '10',
                'signed' => false,
                'identity' => 'enable',
                'after' => 'stat_add_date'
            ])
            ->addIndex(['coordinator_conversation_id'], [
                'name' => 'betrieb_FKIndex8_conv_coordinator',
                'unique' => true,
            ])
            ->save();
    }
}
