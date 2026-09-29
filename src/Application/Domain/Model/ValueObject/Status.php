<?php

declare(strict_types=1);

namespace App\Application\Domain\Model\ValueObject;

final readonly class Status
{
    private function __construct(public string $value)
    {
    }

    public static function received(): self
    {
        return new self('received');
    }

    public static function enriching(): self
    {
        return new self('enriching');
    }

    public static function enriched(): self
    {
        return new self('enriched');
    }

    public static function fromString(string $value): self
    {
        return match ($value) {
            'received' => self::received(),
            'enriching' => self::enriching(),
            'enriched' => self::enriched(),
            default => throw new \InvalidArgumentException(sprintf('Invalid status: %s', $value)),
        };
    }
}
