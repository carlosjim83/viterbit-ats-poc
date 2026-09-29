<?php

declare(strict_types=1);

namespace App\Application\Application\Query;

final readonly class GetApplicationDetail
{
    public function __construct(
        public string $applicationId,
    ) {
    }
}
