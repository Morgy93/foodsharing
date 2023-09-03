<?php

namespace Foodsharing\Modules\Core\DBConstants\Quiz;

use ReflectionClass;
use ReflectionClassConstant;

class QuizIDs
{
    public const FOODSAVER = 1;
    public const STORE_MANAGER = 2;
    public const AMBASSADOR = 3;
    public const KEY_ACCOUNT_MANAGER = 4;
    public const HYGIENE = 5;

    public static function getConstants()
    {
        $reflectionClass = new ReflectionClass(__CLASS__);
        return $reflectionClass->getConstants(ReflectionClassConstant::IS_PUBLIC);
    }
}
