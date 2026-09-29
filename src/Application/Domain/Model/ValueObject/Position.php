<?php

declare(strict_types=1);

namespace App\Application\Domain\Model\ValueObject;

final readonly class Position
{
    public function __construct(public string $value)
    {
        if ('' === trim($value)) {
            throw new \InvalidArgumentException('Position cannot be empty');
        }
    }
}
