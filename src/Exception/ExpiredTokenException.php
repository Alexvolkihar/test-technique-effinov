<?php

declare(strict_types=1);

namespace App\Exception;

class ExpiredTokenException extends \RuntimeException implements DomainExceptionInterface
{
    public function __construct(string $message = "Ce lien de validation a expiré.")
    {
        parent::__construct($message);
    }
}
