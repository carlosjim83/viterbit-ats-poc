<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\LLM;

use App\Application\Domain\LLMClientInterface;

final readonly class MockLLMClient implements LLMClientInterface
{
    public function enrich(string $cvText, string $position): array
    {
        $keywords = $this->extractKeywords($cvText);
        $summary = sprintf(
            'Candidate for %s with expertise in %s.',
            $position,
            implode(', ', $keywords) ?: 'general skills'
        );

        $score = $this->calculateScore($cvText);

        return [
            'summary' => $summary,
            'score' => $score,
        ];
    }

    /**
     * @return list<string>
     */
    private function extractKeywords(string $cvText): array
    {
        $knownKeywords = ['PHP', 'Python', 'JavaScript', 'React', 'Symfony', 'Docker', 'AWS', 'SQL', 'TypeScript'];
        $found = [];

        foreach ($knownKeywords as $keyword) {
            if (false !== stripos($cvText, $keyword)) {
                $found[] = $keyword;
            }
        }

        return $found;
    }

    private function calculateScore(string $cvText): int
    {
        return min(100, (int) round(sqrt(strlen($cvText)) * 5));
    }
}
