<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddFeatureToggleTable extends AbstractMigration
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
        $featureToggleTable = $this->table('fs_feature_toggles', [
            'id' => false,
            'primary_key' => ['identifier', 'site_environment'],
        ]);

        $featureToggleTable->addColumn('identifier', 'string', [
            'limit' => 255,
        ]);
        $featureToggleTable->addIndex('identifier');

        $featureToggleTable->addColumn('is_active', 'boolean', [
            'limit' => 1,
            'default' => 0,
        ]);

        $featureToggleTable->addColumn('site_environment', 'string', [
            'limit' => 255,
        ]);

        $featureToggleTable->addTimestamps();

        $featureToggleTable->create();
    }
}
