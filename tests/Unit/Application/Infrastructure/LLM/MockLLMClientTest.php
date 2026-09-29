<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Infrastructure\LLM;

use App\Application\Infrastructure\LLM\MockLLMClient;
use PHPUnit\Framework\TestCase;

final class MockLLMClientTest extends TestCase
{
    public function testItImplementsLLMClientInterface(): void
    {
        $client = new MockLLMClient();

        self::assertInstanceOf(\App\Application\Domain\LLMClientInterface::class, $client);
    }

    public function testItReturnsSummaryAndScore(): void
    {
        $client = new MockLLMClient();

        /** @var array{summary: string, score: int} $result */
        $result = $client->enrich('Experienced developer with Python and PHP.', 'Backend Engineer');

        self::assertArrayHasKey('summary', $result);
        self::assertArrayHasKey('score', $result);
        self::assertNotEmpty($result['summary']);
    }

    public function testScoreIsDeterministic(): void
    {
        $client = new MockLLMClient();

        /** @var array{summary: string, score: int} $result1 */
        $result1 = $client->enrich('Short CV.', 'Developer');
        /** @var array{summary: string, score: int} $result2 */
        $result2 = $client->enrich('Short CV.', 'Developer');

        self::assertSame($result1['score'], $result2['score']);
    }

    public function testScoreIsWithinRange(): void
    {
        $client = new MockLLMClient();

        /** @var array{summary: string, score: int} $result */
        $result = $client->enrich(str_repeat('word ', 500), 'Manager');

        self::assertGreaterThanOrEqual(0, $result['score']);
        self::assertLessThanOrEqual(100, $result['score']);
    }

    public function testSummaryMentionsPosition(): void
    {
        $client = new MockLLMClient();

        /** @var array{summary: string, score: int} $result */
        $result = $client->enrich('Some CV text here.', 'Data Scientist');

        self::assertStringContainsString('Data Scientist', $result['summary']);
    }
}
