<?php

namespace Foodsharing\RestApi\Models\QueryParser;

class BasicFilterQuery
{
    public function __construct(
        public readonly string $field = '',
        public readonly ?string $operator = '',
        public readonly ?array $values = []
    ) {
    }

    public static function decodeRawQuery(string $raw): BasicFilterQuery
    {
        $elements = explode(':', $raw);
        $field = array_shift($elements);
        $operator = strtolower(array_shift($elements));

        $parameters = [];
        foreach ($elements as $element) {
            $parameters = array_merge($parameters, explode(',', $element));
        }

        return new BasicFilterQuery($field, $operator, $parameters);
    }
}
