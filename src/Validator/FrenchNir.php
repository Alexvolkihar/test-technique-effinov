<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class FrenchNir extends Constraint
{
    public string $messageFormat = 'Le numéro de sécurité sociale doit comporter 15 chiffres (ou inclure 2A/2B pour la Corse).';
    public string $messageInvalid = 'Le numéro de sécurité sociale n\'est pas valide.';
    public string $messageMismatch = 'Le numéro de sécurité sociale ne correspond pas à la date de naissance.';
    public string $messageKey = 'La clé de contrôle du numéro de sécurité sociale est incorrecte.';

    /**
     * @param array<string, mixed>|null $options
     * @param array<string>|null $groups
     */
    public function __construct(
        ?array $options = null,
        ?string $messageFormat = null,
        ?string $messageInvalid = null,
        ?string $messageMismatch = null,
        ?string $messageKey = null,
        ?array $groups = null,
        mixed $payload = null
    ) {
        parent::__construct($options ?? [], $groups, $payload);

        $this->messageFormat = $messageFormat ?? $this->messageFormat;
        $this->messageInvalid = $messageInvalid ?? $this->messageInvalid;
        $this->messageMismatch = $messageMismatch ?? $this->messageMismatch;
        $this->messageKey = $messageKey ?? $this->messageKey;
    }
}
