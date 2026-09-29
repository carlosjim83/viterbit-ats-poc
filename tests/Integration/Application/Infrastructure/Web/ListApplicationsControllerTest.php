<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\Infrastructure\Web;

use App\Tests\WebDatabaseTestCase;

final class ListApplicationsControllerTest extends WebDatabaseTestCase
{
    public function testListPageLoadsWithFilters(): void
    {
        $client = static::createClient();
        $client->request('GET', '/applications');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
        self::assertSelectorExists('select[name="status"]');
        self::assertSelectorExists('input[name="position"]');
        self::assertSelectorExists('input[name="search"]');
        self::assertSelectorTextContains('h1', 'Job Applications');
    }

    public function testListShowsApplications(): void
    {
        $client = static::createClient();
        $this->givenApplication('Ada Lovelace', 'ada@example.com', 'Engineer');

        $client->request('GET', '/applications');

        self::assertResponseIsSuccessful();
        $html = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('Ada Lovelace', $html);
        self::assertStringContainsString('ada@example.com', $html);
        self::assertStringContainsString('Engineer', $html);
    }

    public function testListFiltersByPosition(): void
    {
        $client = static::createClient();
        $this->givenApplication('Alice', 'alice@example.com', 'Engineer');
        $this->givenApplication('Bob', 'bob@example.com', 'Manager');

        $client->request('GET', '/applications?position=Engineer');

        self::assertResponseIsSuccessful();
        $html = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('Alice', $html);
        self::assertStringNotContainsString('Bob', $html);
    }

    public function testListFiltersBySearch(): void
    {
        $client = static::createClient();
        $this->givenApplication('Alice Smith', 'alice@example.com', 'Engineer');
        $this->givenApplication('Bob Jones', 'bob@example.com', 'Manager');

        $client->request('GET', '/applications?search=alice%40example.com');

        self::assertResponseIsSuccessful();
        $html = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('Alice Smith', $html);
        self::assertStringNotContainsString('Bob Jones', $html);
    }

    public function testListShowsEmptyMessage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/applications');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('p', 'No applications found.');
    }
}
