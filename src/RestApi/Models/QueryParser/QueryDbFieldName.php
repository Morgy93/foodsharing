<?php

namespace Foodsharing\RestApi\Models\QueryParser;

use Attribute;

#[Attribute]
class QueryDbFieldName
{
    public function __construct(public readonly string $fieldname)
    {
    }
}
