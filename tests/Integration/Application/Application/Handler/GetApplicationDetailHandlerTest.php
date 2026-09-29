<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\Application\Handler;

use App\Application\Application\Query\GetApplicationDetail\GetApplicationDetail;
use App\Application\Application\Query\GetApplicationDetail\GetApplicationDetailHandler;
use App\Tests\DatabaseTestCase;

final class GetApplicationDetailHandlerTest extends DatabaseTestCase
{
    private GetApplicationDetailHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = new GetApplicationDetailHandler($this->doctrineJobApplicationRepository());
    }

    public function testItReturnsDetailDtoForExistingApplication(): void
    {
        $application = $this->givenApplication('Ada Lovelace', 'ada@example.com', 'Engineer');

        $dto = $this->handler->handle(new GetApplicationDetail((string) $application->id));

        self::assertNotNull($dto);
        self::assertSame((string) $application->id, $dto->id);
        self::assertSame('Ada Lovelace', $dto->fullName);
        self::assertSame('ada@example.com', $dto->email);
        self::assertSame('Engineer', $dto->position);
    }

    public function testItReturnsNullForUnknownApplication(): void
    {
        $dto = $this->handler->handle(new GetApplicationDetail('550e8400-e29b-41d4-a716-446655440000'));

        self::assertNull($dto);
    }
}
