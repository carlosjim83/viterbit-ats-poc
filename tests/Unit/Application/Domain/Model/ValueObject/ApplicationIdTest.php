<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Domain\Model\ValueObject;

use App\Application\Domain\Model\ValueObject\ApplicationId;
use PHPUnit\Framework\TestCase;

final class ApplicationIdTest extends TestCase
{
    public function testGeneratesUniqueId(): void
    {
        $id1 = ApplicationId::generate();
        $id2 = ApplicationId::generate();

        self::assertNotSame((string) $id1, (string) $id2);
    }

    public function testEqualsSameValue(): void
    {
        $id = ApplicationId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $same = ApplicationId::fromString('550e8400-e29b-41d4-a716-446655440000');

        self::assertTrue($id->equals($same));
    }

    public function testRejectsInvalidUuid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ApplicationId::fromString('not-a-uuid');
    }
}
