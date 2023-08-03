<?php

namespace Foodsharing\RestApi\Models\QueryParser;

use Attribute;

#[Attribute]
class SupportedQueryConditionStrategy
{
    public function __construct(public readonly array $typenames)
    {
    }
}
