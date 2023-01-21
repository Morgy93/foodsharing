<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddStoreConversationIndices extends AbstractMigration
{
    public function change(): void
    {
        $this->table('fs_betrieb')
            ->addIndex(['team_conversation_id'], [
                'name' => 'betrieb_FKIndex6_conv'
            ])
            ->addIndex(['springer_conversation_id'], [
                'name' => 'betrieb_FKIndex7_conv_spring'
            ])
            ->update();
    }
}
