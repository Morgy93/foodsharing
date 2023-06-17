<?php

namespace Foodsharing\Validator;

use Parsedown;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class NoMarkdownValidator extends ConstraintValidator
{
    public function __construct(private readonly Parsedown $parseDown)
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof NoMarkdown) {
            throw new UnexpectedTypeException($constraint, NoMarkdown::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) to take care of that
        if ($value === null || $value === '') {
            return;
        }

        if (!is_string($value)) {
            // throw this exception if your validator cannot handle the passed type so that it can be marked as invalid
            throw new UnexpectedValueException($value, 'string');
            // separate multiple types using pipes
            // throw new UnexpectedValueException($value, 'string|int');
        }

        $escapedValue = $this->parseDown->text($value);
        $escapedValueSkipedContainer = substr($escapedValue, strlen('<p>'), strlen($escapedValue) - (strlen('<p>') + strlen('</p>')));
        if ($escapedValueSkipedContainer != $value) {
            // the argument must be a string or an object implementing __toString()
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
