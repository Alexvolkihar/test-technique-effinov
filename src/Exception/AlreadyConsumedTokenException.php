<?php

declare(strict_types=1);

namespace App\Exception;

class AlreadyConsumedTokenException extends \RuntimeException implements DomainExceptionInterface
{
    public function __construct(string $message = "Ce lien de validation a déjà été utilisé.")
    {
        parent::__construct($message);
    }
}
