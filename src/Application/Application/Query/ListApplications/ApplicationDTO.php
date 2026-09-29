<?php

declare(strict_types=1);

namespace App\Application\Application\Query\ListApplications;

final readonly class ApplicationDTO
{
    public function __construct(
        public string $id,
        public string $fullName,
        public string $email,
        public string $position,
        public string $status,
        public string $appliedAt,
        public ?float $score = null,
    ) {
    }
}
