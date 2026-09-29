<?php

declare(strict_types=1);

namespace App\Application\Application\Command\SubmitApplication;

final readonly class SubmitApplication
{
    public function __construct(
        public string $fullName,
        public string $email,
        public string $phone,
        public string $position,
        public string $notes,
        public string $cvText,
    ) {
    }
}
