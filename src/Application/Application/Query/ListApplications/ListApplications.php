<?php

declare(strict_types=1);

namespace App\Application\Application\Query\ListApplications;

final readonly class ListApplications
{
    public function __construct(
        public ?string $status = null,
        public ?string $position = null,
        public ?string $search = null,
    ) {
    }
}
