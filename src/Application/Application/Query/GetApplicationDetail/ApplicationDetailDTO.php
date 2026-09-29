<?php

declare(strict_types=1);

namespace App\Application\Application\Query\GetApplicationDetail;

final readonly class ApplicationDetailDTO
{
    public function __construct(
        public string $id,
        public string $fullName,
        public string $email,
        public string $phone,
        public string $position,
        public string $notes,
        public string $cvText,
        public string $status,
        public string $appliedAt,
        public ?string $summary = null,
        public ?int $score = null,
    ) {
    }
}
