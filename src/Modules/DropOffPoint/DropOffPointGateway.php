<?php

namespace Foodsharing\Modules\DropOffPoint;

use Exception;
use Foodsharing\Modules\Core\BaseGateway;

class DropOffPointGateway extends BaseGateway
{
	const DROP_OFF_POINT_TABLE_NAME = 'fs_drop_off_point';

	/**
	 * TODO-810: full description please.
	 *
	 * @param int $id identifier of the drop-off-point to be fetched
	 *
	 * @return array array with all information that is fetched
	 *
	 * @throws Exception
	 */
	public function getDropOffPoint(int $id): array
	{
		//TODO-810: access database with fetchById and not plain SQL and return DTO instead of array
//		$dropOffPoint = $this->db->fetchById(self::DROP_OFF_POINT_TABLE_NAME, ['name'], $id);
		return $this->db->fetch('SELECT name, description FROM fs_drop_off_point WHERE id = 5');
	}
}
