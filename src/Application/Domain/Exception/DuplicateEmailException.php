<?php

declare(strict_types=1);

namespace App\Application\Domain\Exception;

final class DuplicateEmailException extends \DomainException
{
    public function __construct(string $email)
    {
        parent::__construct(sprintf('An application with email %s already exists.', $email));
    }
}
