<?php

namespace Foodsharing\Modules\Unit;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Core\DBConstants\Unit\UnitType;
use Foodsharing\Modules\Unit\DTO\UserUnit;

/**
 * The gateway is a CRUD helper to do stuff on the database table which represents a unit like (regions or groups).
 */
class UnitGateway extends BaseGateway
{
    public function __construct(
        Database $db
    ) {
        parent::__construct($db);
    }

    /**
     * Fetch all units of the foodsaver with information about the responsibility of the user.
     * This can be filtered by the unit types @see UnitType.
     *
     * @param int $foodsaverId Identifier of the foodsaver user
     * @param int[] $unitTypes List of unit types should be provided if present (@See UnitType)
     *
     * @return UserUnit[] List of unit
     */
    public function listAllDirectReleatedUnitsAndResponsibilitiesOfFoodsaver(int $foodsaverId, array $unitTypes): array
    {
        foreach ($unitTypes as $unittype) {
            UnitType::throwIfInvalid($unittype);
        }

        $inPlaceHolder = implode(', ', array_fill(0, count($unitTypes), '?'));
        $rows = $this->db->fetchAll(
            'SELECT unit.id, unit.name, unit.type, responsible.bezirk_id is not null as isResponsible from fs_foodsaver_has_bezirk as foodsaver
			  INNER JOIN fs_bezirk as unit ON foodsaver.bezirk_id = unit.id
			  LEFT JOIN fs_botschafter as responsible ON foodsaver.bezirk_id = responsible.bezirk_id and foodsaver.foodsaver_id = responsible.foodsaver_id
			  WHERE foodsaver.foodsaver_id = ? 
			    AND unit.type in (' . $inPlaceHolder . ')
			  ORDER BY type, isResponsible DESC, name',
            [
                $foodsaverId,
                $unitTypes
            ]
        );

        $results = [];
        foreach ($rows as $row) {
            $results[] = UserUnit::createFromArray($row);
        }

        return $results;
    }
}
