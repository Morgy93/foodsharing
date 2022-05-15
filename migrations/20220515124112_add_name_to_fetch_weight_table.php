<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class addNameToFetchWeightTable extends AbstractMigration
{
	public function change(): void
	{
		$this->table('fs_fetchweight')
				->addColumn('name', 'string', [
				'null' => true,
				'limit' => '50',
				'signed' => false,
				'comment' => 'name of weight categorie'
			])
			->update();
	}
}
