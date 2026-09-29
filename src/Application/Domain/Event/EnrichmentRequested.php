<?php

declare(strict_types=1);

namespace App\Application\Domain\Event;

use App\Application\Domain\Model\ValueObject\ApplicationId;

final readonly class EnrichmentRequested
{
    public function __construct(
        public ApplicationId $applicationId,
        public \DateTimeImmutable $occurredOn = new \DateTimeImmutable(),
    ) {
    }
}
