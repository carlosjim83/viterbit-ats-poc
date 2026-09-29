<?php

declare(strict_types=1);

namespace App\Application\Application\Command;

final readonly class SubmitApplication
{
    public function __construct(
        public string $fullName,
        public string $email,
        public string $position,
        public string $cvText,
    ) {
    }
}
