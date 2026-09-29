<?php

declare(strict_types=1);

namespace App\Application\Domain;

interface LLMClientInterface
{
    /**
     * @return array{summary: string, score: int}
     */
    public function enrich(string $cvText, string $position): array;
}
