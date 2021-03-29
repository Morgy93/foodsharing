<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddStoreChainForeignKey extends AbstractMigration
{
	public function change(): void
	{
		// set all 0s to NULL
		$this->query('UPDATE fs_betrieb SET kette_id=NULL WHERE kette_id=0');

		// add foreign key
		$this->table('fs_betrieb')
			->addForeignKey('kette_id', 'fs_kette', 'id', ['delete' => 'SET NULL'])
			->update();
	}
}
