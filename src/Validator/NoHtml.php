<?php

namespace Foodsharing\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class NoHtml extends Constraint
{
    public string $message = 'The value contains an html tags which are not allowed';
}
