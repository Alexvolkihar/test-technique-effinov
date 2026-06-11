<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class UniqueRegistrationField extends Constraint
{
    public string $message = 'Cette valeur est déjà utilisée.';
    public string $field;

    /**
     * @param array<string, mixed>|null $options
     * @param array<string>|null $groups
     */
    public function __construct(?array $options = null, ?string $field = null, ?string $message = null, ?array $groups = null, mixed $payload = null)
    {
        parent::__construct($options ?? [], $groups, $payload);

        $this->field = $field ?? $options['field'] ?? '';
        if ($message !== null) {
            $this->message = $message;
        }

        if (empty($this->field)) {
            throw new \InvalidArgumentException('The "field" option must be specified.');
        }
    }

    public function getDefaultOption(): ?string
    {
        return 'field';
    }

    public function getRequiredOptions(): array
    {
        return ['field'];
    }
}
