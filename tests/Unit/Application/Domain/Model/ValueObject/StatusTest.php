<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Domain\Model\ValueObject;

use App\Application\Domain\Model\ValueObject\Status;
use PHPUnit\Framework\TestCase;

final class StatusTest extends TestCase
{
    public function testReceivedStatus(): void
    {
        $status = Status::received();

        self::assertSame('received', $status->value);
    }

    public function testEnrichingStatus(): void
    {
        $status = Status::enriching();

        self::assertSame('enriching', $status->value);
    }

    public function testEnrichedStatus(): void
    {
        $status = Status::enriched();

        self::assertSame('enriched', $status->value);
    }
}
