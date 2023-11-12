<?php

namespace Foodsharing\Modules\Statistics\DTO;

/**
 * Represents one entry in the result of age band Region query.
 */
class StatisticsAgeBand
{
    public string $ageBand;
    public int $numberOfAgeBand;

    public static function create(string $ageBand, int $numberOfAgeBand): StatisticsAgeBand
    {
        $c = new StatisticsAgeBand();
        $c->ageBand = $ageBand;
        $c->numberOfAgeBand = $numberOfAgeBand;

        return $c;
    }
}
