<?php

namespace Foodsharing\Modules\Core;

use Symfony\Component\Validator\Constraints as Assert;

class Pagination
{
    /**
     * Count of item per page.
     *
     * @Assert\Positive()
     */
    public int $pageSize = 20;

    /**
     * Offset to start.
     *
     * @Assert\Positive()
     */
    public int  $offset = 0;

    public function buildSqlLimit(): string
    {
        return ' LIMIT :page_size OFFSET :start_item_index ';
    }

    public function addSqlLimitParameters(array $params): array
    {
        $params['start_item_index'] = $this->offset;
        $params['page_size'] = $this->pageSize;

        return $params;
    }
}
