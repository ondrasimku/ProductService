<?php

namespace App\Validator;

use App\Validator\Constraint\ValidPassword;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ValidPasswordValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint)
    {
        if (!$constraint instanceof ValidPassword) {
            throw new UnexpectedTypeException($constraint, ValidPassword::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $pattern = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,}$/';

        if (!preg_match($pattern, $value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
