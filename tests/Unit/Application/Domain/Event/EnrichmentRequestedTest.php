<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Domain\Event;

use App\Application\Domain\Event\EnrichmentRequested;
use App\Application\Domain\Model\ValueObject\ApplicationId;
use PHPUnit\Framework\TestCase;

final class EnrichmentRequestedTest extends TestCase
{
    public function testItStoresApplicationId(): void
    {
        $id = ApplicationId::generate();
        $event = new EnrichmentRequested($id);

        self::assertTrue($id->equals($event->applicationId));
    }

    public function testItSetsDefaultOccurredOn(): void
    {
        $before = new \DateTimeImmutable();
        $event = new EnrichmentRequested(ApplicationId::generate());
        $after = new \DateTimeImmutable();

        self::assertGreaterThanOrEqual($before, $event->occurredOn);
        self::assertLessThanOrEqual($after, $event->occurredOn);
    }
}
