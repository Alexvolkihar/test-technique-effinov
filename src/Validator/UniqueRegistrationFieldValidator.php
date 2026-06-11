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

        if (null === $value) {
            return;
        }

        if ($value instanceof \App\Entity\RegistrationRequest) {
            $field = $constraint->field;
            $getter = 'get' . ucfirst($field);
            if (!method_exists($value, $getter)) {
                throw new \InvalidArgumentException(sprintf('Getter method "%s" does not exist on class %s', $getter, $value::class));
            }
            $fieldValue = $value->$getter();
        } else {
            $fieldValue = $value;
        }

        if (null === $fieldValue || '' === $fieldValue) {
            return;
        }

        $existing = $this->repository->findOneBy([$constraint->field => $fieldValue]);

        if ($existing !== null) {
            foreach ($this->context->getViolations() as $violation) {
                if ($violation->getMessage() === $constraint->message) {
                    return;
                }
            }

            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
