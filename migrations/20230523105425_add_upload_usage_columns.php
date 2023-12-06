<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddUploadUsageColumns extends AbstractMigration
{
    public function change(): void
    {
        $this->table('uploads')
            ->addColumn('used_in', 'integer', [
                'null' => true,
                'signed' => false,
                'limit' => 4,
                'default' => null,
                'comment' => 'Indicates in which module this uploaded file is being used (profile photo, wall post, ...). A value of null indicates that the file is not being used (yet).'
            ])
            ->addColumn('usage_id', 'char', [
                'null' => true,
                'signed' => false,
                'limit' => 10,
                'default' => null,
                'comment' => 'Id of the entity that uses this uploaded file, e.g. id of the profile or the wall post. A null value indicates that the file is not being used (yet).'
            ])
            ->save();
    }
}
