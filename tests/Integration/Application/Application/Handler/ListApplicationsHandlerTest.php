<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\Application\Handler;

use App\Application\Application\Handler\ListApplicationsHandler;
use App\Application\Application\Query\ListApplications;
use App\Tests\DatabaseTestCase;

final class ListApplicationsHandlerTest extends DatabaseTestCase
{
    private ListApplicationsHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = new ListApplicationsHandler($this->doctrineJobApplicationRepository());
    }

    public function testItReturnsApplicationDTOsFromDatabase(): void
    {
        $app = $this->givenApplication('Ada Lovelace', 'ada@example.com', 'Engineer');

        $result = $this->handler->handle(new ListApplications());

        self::assertCount(1, $result);
        self::assertSame('Ada Lovelace', $result[0]->fullName);
        self::assertSame('Engineer', $result[0]->position);
    }

    public function testItFiltersByPosition(): void
    {
        $this->givenApplication('Alice', 'alice@example.com', 'Engineer');
        $this->givenApplication('Bob', 'bob@example.com', 'Manager');

        $result = $this->handler->handle(new ListApplications(position: 'Engineer'));

        self::assertCount(1, $result);
        self::assertSame('Alice', $result[0]->fullName);
    }

    public function testItSearchesByName(): void
    {
        $this->givenApplication('Alice Smith', 'alice@example.com');
        $this->givenApplication('Bob Jones', 'bob@example.com');

        $result = $this->handler->handle(new ListApplications(search: 'Alice'));

        self::assertCount(1, $result);
        self::assertSame('Alice Smith', $result[0]->fullName);
    }

    public function testItReturnsNewestFirst(): void
    {
        $this->givenApplication('First', 'first@example.com');
        sleep(1);
        $this->givenApplication('Second', 'second@example.com');

        $result = $this->handler->handle(new ListApplications());

        self::assertCount(2, $result);
        self::assertSame('Second', $result[0]->fullName);
        self::assertSame('First', $result[1]->fullName);
    }
}
