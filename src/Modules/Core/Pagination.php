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
    public ?int $pageSize = null;

    /**
     * Offset to start.
     *
     * @Assert\Positive()
     */
    public int $offset = 0;

    public function buildSqlLimit(): string
    {
        if ($this->pageSize || $this->pageSize != 0) {
            return ' LIMIT :page_size OFFSET :start_item_index ';
        }

        return '';
    }

    public function addSqlLimitParameters(array $params): array
    {
        if ($this->pageSize || $this->pageSize != 0) {
            $params['start_item_index'] = $this->offset;
            $params['page_size'] = $this->pageSize;
        }

        return $params;
    }
}
