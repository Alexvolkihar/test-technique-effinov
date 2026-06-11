<?php

declare(strict_types=1);

namespace App\Exception;

class InvalidTokenException extends \InvalidArgumentException implements DomainExceptionInterface
{
    public function __construct(string $message = "Ce lien de validation est invalide.")
    {
        parent::__construct($message);
    }
}
