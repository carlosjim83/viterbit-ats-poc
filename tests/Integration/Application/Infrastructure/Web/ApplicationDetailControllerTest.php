<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\Infrastructure\Web;

use App\Tests\WebDatabaseTestCase;

final class ApplicationDetailControllerTest extends WebDatabaseTestCase
{
    public function testDetailPageShowsApplication(): void
    {
        $client = static::createClient();
        $application = $this->givenApplication('Ada Lovelace', 'ada@example.com', 'Engineer');

        $client->request('GET', '/applications/'.$application->id);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Application Detail');

        $html = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('Ada Lovelace', $html);
        self::assertStringContainsString('ada@example.com', $html);
        self::assertStringContainsString('Engineer', $html);
    }

    public function testDetailPageReturns404ForUnknownApplication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/applications/550e8400-e29b-41d4-a716-446655440000');

        self::assertResponseStatusCodeSame(404);
    }
}
