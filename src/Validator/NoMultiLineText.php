<?php

namespace Foodsharing\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class NoMultiLineText extends Constraint
{
    public string $message = 'The value contains an a multi line string';
}
