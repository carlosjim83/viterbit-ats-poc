<?php

declare(strict_types=1);

namespace App\Application\Domain\Model\ValueObject;

final readonly class Phone
{
    public function __construct(public string $value)
    {
        if ('' === trim($value)) {
            throw new \InvalidArgumentException('Phone cannot be empty');
        }
    }
}
