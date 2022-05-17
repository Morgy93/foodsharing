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
		$this->table('fs_fetchweight')->truncate();
		$this->table('fs_fetchweight')->insert([
			['id' => '0', 'weight' => '0', 'name' => 'Keine Angabe'],
			['id' => '1', 'weight' => '2.0', 'name' => '1-3 kg'],
			['id' => '2', 'weight' => '4.0', 'name' => '3-5 kg'],
			['id' => '3', 'weight' => '7.5', 'name' => '5-10 kg'],
			['id' => '4', 'weight' => '15', 'name' => '10-20 kg'],
			['id' => '5', 'weight' => '25', 'name' => '20-30 kg'],
			['id' => '6', 'weight' => '35', 'name' => '30-40 kg'],
			['id' => '7', 'weight' => '45', 'name' => '40-50 kg'],
			['id' => '8', 'weight' => '62.5', 'name' => '50-75 kg'],
			['id' => '9', 'weight' => '87.5', 'name' => '75-100 kg'],
			['id' => '10', 'weight' => '110', 'name' => 'mehr als 100 kg']
		])->save();

	}
}
