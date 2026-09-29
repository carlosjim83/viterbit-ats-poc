<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Domain\Event;

use App\Application\Domain\Event\ApplicationSubmitted;
use App\Application\Domain\Model\ValueObject\ApplicationId;
use PHPUnit\Framework\TestCase;

final class ApplicationSubmittedTest extends TestCase
{
    public function testEventContainsApplicationIdAndTimestamp(): void
    {
        $id = ApplicationId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $event = new ApplicationSubmitted($id);

        self::assertTrue($id->equals($event->applicationId));
        self::assertInstanceOf(\DateTimeImmutable::class, $event->occurredOn);
    }
}
