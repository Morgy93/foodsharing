<?php

namespace Foodsharing\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class NoMarkdown extends Constraint
{
    public string $message = 'The value contains markdown markup which is not allowed.';
}
