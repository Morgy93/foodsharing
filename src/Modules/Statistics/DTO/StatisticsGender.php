<?php

namespace Foodsharing\Modules\Statistics\DTO;

/**
 * Represents one entry in the result of gender Region query.
 */
class StatisticsGender
{
    public int $gender;

    public int $numberOfGender;

    public static function create(int $gender, int $numberOfGender): StatisticsGender
    {
        $c = new StatisticsGender();
        $c->gender = $gender;
        $c->numberOfGender = $numberOfGender;

        return $c;
    }
}
