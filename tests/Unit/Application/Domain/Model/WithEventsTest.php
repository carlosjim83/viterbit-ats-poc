<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Domain\Model;

use App\Shared\Domain\Model\WithEvents;
use PHPUnit\Framework\TestCase;

final class WithEventsTest extends TestCase
{
    public function testItRecordsAndReturnsEvents(): void
    {
        $aggregate = new class {
            use WithEvents;

            public function apply(object $event): void
            {
                $this->recordEvent($event);
            }
        };

        $event = new \stdClass();
        $aggregate->apply($event);

        self::assertSame([$event], $aggregate->events());
    }

    public function testItClearsEventsAfterRetrieval(): void
    {
        $aggregate = new class {
            use WithEvents;

            public function apply(object $event): void
            {
                $this->recordEvent($event);
            }
        };

        $aggregate->apply(new \stdClass());
        $aggregate->events();

        self::assertSame([], $aggregate->events());
    }
}
