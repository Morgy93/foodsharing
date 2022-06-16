<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateUntilColumnFromBasketsTableToDatetime extends AbstractMigration
{
	public function change(): void
	{
		$this->query('alter table fs_basket modify until datetime not null;');
	}
}
