<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\RegistrationRequest;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class FrenchNirValidator extends ConstraintValidator
{
    public function __construct(
        private bool $strictNirValidation
    ) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof FrenchNir) {
            throw new UnexpectedTypeException($constraint, FrenchNir::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        // Strip spaces, dashes, and dots
        $normalizedNir = str_replace([' ', '-', '.'], '', strtoupper($value));

        if (!$this->strictNirValidation) {
            // Non-strict mode: check 15-character length
            if (strlen($normalizedNir) !== 15) {
                $this->context->buildViolation($constraint->messageFormat)
                    ->addViolation();
            }
            return;
        }

        // Format validation (15 characters)
        // 1 or 2 (gender)
        // 2 digits (year YY)
        // 2 digits (month MM: 01-12 or 20-99)
        // 2 digits/chars (department: 01-99 or 2A/2B)
        // 6 digits (commune + order)
        // 2 digits (key)
        $pattern = '/^[12][0-9]{2}(0[1-9]|1[0-2]|[2-9][0-9])(2A|2B|[0-9]{2})[0-9]{6}[0-9]{2}$/i';
        if (!preg_match($pattern, $normalizedNir)) {
            $this->context->buildViolation($constraint->messageFormat)
                ->addViolation();
            return;
        }

        // Get the parent object to compare with birth date
        $object = $this->context->getObject();
        if ($object instanceof RegistrationRequest) {
            $birthDate = $object->getBirthDate();
            if ($birthDate instanceof \DateTimeInterface) {
                $expectedYear = $birthDate->format('y');
                $expectedMonth = $birthDate->format('m');

                $actualYear = substr($normalizedNir, 1, 2);
                $actualMonth = substr($normalizedNir, 3, 2);

                if ($actualYear !== $expectedYear || $actualMonth !== $expectedMonth) {
                    $this->context->buildViolation($constraint->messageMismatch)
                        ->addViolation();
                    return;
                }
            }
        }

        // Validate the control key
        $nir13 = substr($normalizedNir, 0, 13);
        $expectedKey = (int) substr($normalizedNir, 13, 2);

        $dept = substr($normalizedNir, 5, 2);
        $nir13Numeric = $nir13;

        if ($dept === '2A') {
            $nir13Numeric = substr_replace($nir13Numeric, '20', 5, 2);
            $nir13NumericInt = (int) $nir13Numeric - 1000000;
        } elseif ($dept === '2B') {
            $nir13Numeric = substr_replace($nir13Numeric, '20', 5, 2);
            $nir13NumericInt = (int) $nir13Numeric - 2000000;
        } else {
            $nir13NumericInt = (int) $nir13Numeric;
        }

        $computedKey = 97 - ($nir13NumericInt % 97);

        if ($computedKey !== $expectedKey) {
            $this->context->buildViolation($constraint->messageKey)
                ->addViolation();
        }
    }
}
