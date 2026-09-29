<?php

declare(strict_types=1);

namespace App\Application\Domain\Model\ValueObject;

final readonly class Email
{
    public function __construct(public string $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException(sprintf('Invalid email: %s', $value));
        }
    }
}
