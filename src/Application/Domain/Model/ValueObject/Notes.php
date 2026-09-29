<?php

declare(strict_types=1);

namespace App\Application\Domain\Model\ValueObject;

final readonly class Notes
{
    public function __construct(public string $value)
    {
    }
}
