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

        $score = $this->calculateScore($cvText, $position);

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

    private function calculateScore(string $cvText, string $position): int
    {
        $baseScore = min(60, (int) round(sqrt(strlen($cvText)) * 4));

        $positionKeywords = $this->extractPositionKeywords($position);
        $matchCount = 0;
        foreach ($positionKeywords as $keyword) {
            if (false !== stripos($cvText, $keyword)) {
                ++$matchCount;
            }
        }

        $relevanceBonus = count($positionKeywords) > 0
            ? (int) round(($matchCount / count($positionKeywords)) * 40)
            : 0;

        return min(100, $baseScore + $relevanceBonus);
    }

    /**
     * @return list<string>
     */
    private function extractPositionKeywords(string $position): array
    {
        $normalized = strtolower($position);
        $keywords = [];

        if (false !== strpos($normalized, 'engineer') || false !== strpos($normalized, 'developer')) {
            $keywords = array_merge($keywords, ['php', 'python', 'javascript', 'symfony', 'react', 'docker', 'aws', 'sql', 'typescript']);
        }
        if (false !== strpos($normalized, 'manager') || false !== strpos($normalized, 'lead')) {
            $keywords = array_merge($keywords, ['leadership', 'team', 'agile', 'scrum', 'management', 'strategy']);
        }
        if (false !== strpos($normalized, 'data') || false !== strpos($normalized, 'analyst')) {
            $keywords = array_merge($keywords, ['python', 'sql', 'aws', 'machine learning', 'statistics']);
        }

        return array_values(array_unique($keywords));
    }
}
