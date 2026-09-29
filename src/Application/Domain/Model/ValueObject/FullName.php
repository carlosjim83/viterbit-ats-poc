<?php

declare(strict_types=1);

namespace App\Application\Domain\Model\ValueObject;

final readonly class FullName
{
    public function __construct(public string $value)
    {
        if ('' === trim($value)) {
            throw new \InvalidArgumentException('Full name cannot be empty');
        }
    }
}
