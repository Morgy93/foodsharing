<?php

use Phinx\Migration\AbstractMigration;

class RemoveEventBot extends AbstractMigration
{
	public function up()
	{
		$table = $this->table('fs_event');
		$table->removeColumn('bot')
			  ->save();
	}
}
