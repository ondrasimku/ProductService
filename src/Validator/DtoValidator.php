<?php

namespace App\Validator;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DtoValidator
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param mixed $dto
     * @return ConstraintViolationListInterface|array<mixed>
     */
    public function validate(mixed $dto): ConstraintViolationListInterface|array
    {
        return $this->validator->validate($dto);
    }
}
