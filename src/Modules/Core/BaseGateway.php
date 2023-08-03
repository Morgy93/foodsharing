<?php

namespace Foodsharing\Modules\Core;

abstract class BaseGateway
{
    /**
     * @var Database
     */
    protected $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function buildPaginationSqlLimit(Pagination $pagination, bool $named = true): string
    {
        if ($pagination->pageSize || $pagination->pageSize != 0) {
            if ($named) {
                return ' LIMIT :page_size OFFSET :start_item_index ';
            } else {
                return ' LIMIT ? OFFSET ? ';
            }
        }

        return '';
    }

    public function addPaginationSqlLimitParameters(Pagination $pagination, array $params, bool $named = true): array
    {
        if ($pagination->pageSize || $pagination->pageSize != 0) {
            if ($named) {
                $params['page_size'] = $pagination->pageSize;
                $params['start_item_index'] = $pagination->offset;
            } else {
                $params[] = $pagination->pageSize;
                $params[] = $pagination->offset;
            }
        }

        return $params;
    }
}
