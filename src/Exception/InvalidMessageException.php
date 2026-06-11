<?php

declare(strict_types=1);

namespace App\Exception;

class InvalidMessageException extends \InvalidArgumentException implements DomainExceptionInterface
{
}
