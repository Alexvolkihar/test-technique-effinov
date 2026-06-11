<?php

declare(strict_types=1);

namespace App\Validator;

use App\Repository\RegistrationRequestRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueRegistrationFieldValidator extends ConstraintValidator
{
    public function __construct(
        private RegistrationRequestRepository $repository
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueRegistrationField) {
            throw new UnexpectedTypeException($constraint, UniqueRegistrationField::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        $existing = $this->repository->findOneBy([$constraint->field => $value]);

        // If entity is being edited, we should check it's not the same entity.
        // In our case, we only register new applications, so any match is a violation.
        if ($existing !== null) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
