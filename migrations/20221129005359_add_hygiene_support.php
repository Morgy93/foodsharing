<?php

declare(strict_types=1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

final class AddHygieneSupport extends AbstractMigration
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
		$this->table('fs_betrieb')
			->addColumn('food_hygiene_requirement', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
			])
			->update();

		$this->table('fs_foodsaver')
            ->addColumn('health_authority_instructed', 'date', [
                'null' => true,
                'default' => null,
            ])
            ->addColumn('last_food_hygiene_training', 'date', [
                'null' => true,
                'default' => null,
            ])
            ->update();
	}
}
