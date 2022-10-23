<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddStoreLogIndex extends AbstractMigration
{
	public function change(): void
	{
		$this->table('fs_store_log')
			->addIndex(['store_id', 'date_reference'], [
				'name' => 'store_id_date_ref',
				'unique' => false,
			])
			->addIndex(['store_id', 'date_activity'], [
				'name' => 'store_id_date_act',
				'unique' => false,
			])
			->addIndex(['date_reference', 'date_activity'], [
				'name' => 'date_ref_date_act',
				'unique' => false,
			])
			->save();
	}
}
