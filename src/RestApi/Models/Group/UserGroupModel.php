<?php

namespace Foodsharing\RestApi\Models\Group;

use Foodsharing\Modules\Unit\DTO\UserUnit;
use OpenApi\Annotations as OA;

/**
 * Provides information about the groups of an user.
 *
 * @OA\Schema()
 */
class UserGroupModel
{
    /**
     *  Identifier of group.
     *
     * @OA\Property(example="1"))
     */
    public int $id = 0;

    /**
     * Name of group.
     *
     * @OA\Property(example="Öffentlichkeitsarbeit - Startseite")
     */
    public string $name = '';

    /**
     * Is responsible user.
     *
     * - False: Normal member
     * - True: Is admin of group
     *
     * @OA\Property()
     */
    public bool $isResponsible = false;

    public static function createFrom(UserUnit $UserUnit)
    {
        $userGroupModel = new UserGroupModel();
        $userGroupModel->id = $UserUnit->unit->id;
        $userGroupModel->name = $UserUnit->unit->name;
        $userGroupModel->isResponsible = $UserUnit->isResponsible;

        return $userGroupModel;
    }
}
