<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class PublicityUndecided extends AbstractMigration
{
	/**
	 * Change Method.
	 *
	 * Write your reversible migrations using this method.
	 *
	 * More information on writing migrations is available here:
	 * https://book.cakephp.org/phinx/0/en/migrations.html
	 *
	 * The following commands can be used in this method and Phinx will
	 * automatically reverse them when rolling back:
	 *
	 *    createTable
	 *    renameTable
	 *    addColumn
	 *    addCustomColumn
	 *    renameColumn
	 *    addIndex
	 *    addForeignKey
	 *
	 * Any other destructive changes will result in an error when trying to
	 * rollback the migration.
	 *
	 * Remember to call "create()" or "update()" and NOT "save()" when working
	 * with the Table class.
	 */
	public function change()
	{
		/**
		 * #33 / !1469 rewrite store-edit:.
		 *
		 * allow NULL for two store fields that should support "undecided" / "not yet clarified" values
		 */
		$table = $this->table('fs_betrieb');
		$table
			->changeColumn('presse', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_TINY])
			->changeColumn('sticker', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_TINY])
			->update();
	}
}
