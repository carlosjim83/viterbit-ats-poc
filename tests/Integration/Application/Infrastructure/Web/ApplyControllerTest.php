<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\Infrastructure\Web;

use App\Tests\WebDatabaseTestCase;

final class ApplyControllerTest extends WebDatabaseTestCase
{
    public function testApplyPageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/apply');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
        self::assertSelectorExists('input[name="fullName"]');
        self::assertSelectorExists('input[name="email"]');
        self::assertSelectorExists('input[name="phone"]');
        self::assertSelectorExists('input[name="position"]');
        self::assertSelectorExists('textarea[name="notes"]');
        self::assertSelectorExists('textarea[name="cvText"]');
    }

    public function testSubmitApplicationCreatesRecord(): void
    {
        $client = static::createClient();

        $client->request('GET', '/apply');
        self::assertResponseIsSuccessful();

        $token = $client->getCrawler()->filter('input[name="_token"]')->attr('value');

        $client->request('POST', '/apply', [
            'fullName' => 'Alice Smith',
            'email' => 'alice@example.com',
            'phone' => '+44123456789',
            'position' => 'Engineering Manager',
            'notes' => 'Remote only',
            'cvText' => '10 years of experience in software engineering',
            '_token' => $token,
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Application Submitted');

        $conn = $this->entityManager()->getConnection();
        $row = $conn->fetchAssociative('SELECT * FROM job_applications WHERE email = ?', ['alice@example.com']);
        self::assertNotFalse($row);
        self::assertSame('Alice Smith', $row['full_name']);
        self::assertSame('Engineering Manager', $row['position']);
        self::assertSame('received', $row['status']);
    }

    public function testSubmitWithMissingFieldsShowsErrors(): void
    {
        $client = static::createClient();

        $client->request('GET', '/apply');
        self::assertResponseIsSuccessful();

        $token = $client->getCrawler()->filter('input[name="_token"]')->attr('value');

        $client->request('POST', '/apply', [
            'fullName' => '',
            'email' => 'not-an-email',
            'phone' => '',
            'position' => '',
            'notes' => '',
            'cvText' => '',
            '_token' => $token,
        ]);

        self::assertResponseIsSuccessful();

        $crawler = $client->getCrawler();
        $errors = $crawler->filter('.error')->each(static fn ($node) => $node->text());

        self::assertContains('Full name is required.', $errors);
        self::assertContains('A valid email is required.', $errors);
        self::assertContains('Phone is required.', $errors);
        self::assertContains('Position is required.', $errors);
        self::assertContains('CV text is required.', $errors);
    }
}
