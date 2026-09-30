<?php

declare(strict_types=1);

namespace App\Tests\Integration\EndToEnd;

use App\Tests\WebDatabaseTestCase;

final class EnrichmentFlowTest extends WebDatabaseTestCase
{
    public function testApplicationIsEnrichedAfterSubmission(): void
    {
        $client = static::createClient();

        $client->request('GET', '/apply');
        self::assertResponseIsSuccessful();

        $token = $client->getCrawler()->filter('input[name="_token"]')->attr('value');

        $client->request('POST', '/apply', [
            'fullName' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'phone' => '+44123456789',
            'position' => 'Engineering Manager',
            'notes' => 'Remote only',
            'cvText' => '10 years of experience in software engineering with PHP and Python',
            '_token' => $token,
        ]);

        self::assertResponseIsSuccessful();

        $conn = $this->entityManager()->getConnection();
        $row = $conn->fetchAssociative('SELECT id FROM job_applications WHERE email = ?', ['ada@example.com']);
        self::assertNotFalse($row);

        $applicationId = $row['id'];
        self::assertIsString($applicationId);

        // Trigger enrichment directly via handler
        $handler = static::getContainer()->get(\App\Application\Application\Event\Enrichment\EnrichmentHandler::class);
        self::assertInstanceOf(\App\Application\Application\Event\Enrichment\EnrichmentHandler::class, $handler);
        $handler->handle(new \App\Application\Domain\Event\EnrichmentRequested(
            \App\Application\Domain\Model\ValueObject\ApplicationId::fromString($applicationId)
        ));

        $updatedRow = $conn->fetchAssociative('SELECT status, summary, score FROM job_applications WHERE id = ?', [$applicationId]);
        self::assertNotFalse($updatedRow);
        self::assertSame('enriched', $updatedRow['status']);
        self::assertNotNull($updatedRow['summary']);
        self::assertNotNull($updatedRow['score']);
        $score = $updatedRow['score'];
        self::assertIsNumeric($score);
        $scoreInt = (int) $score;
        self::assertGreaterThanOrEqual(0, $scoreInt);
        self::assertLessThanOrEqual(100, $scoreInt);
    }
}
