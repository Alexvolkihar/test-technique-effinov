<?php

declare(strict_types=1);

namespace App\Exception;

class InvalidAccountStatusException extends \RuntimeException implements DomainExceptionInterface
{
    public function __construct(string $message = "Ce compte a déjà été activé ou n'est pas en attente de mot de passe.")
    {
        parent::__construct($message);
    }
}
