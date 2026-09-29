<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Application\DTO;

use App\Application\Application\Query\ListApplications\ApplicationDTO;
use PHPUnit\Framework\TestCase;

final class ApplicationDTOTest extends TestCase
{
    public function testItHoldsAllDisplayFields(): void
    {
        $dto = new ApplicationDTO(
            id: 'app-123',
            fullName: 'Ada Lovelace',
            email: 'ada@example.com',
            position: 'Engineer',
            status: 'PENDING',
            appliedAt: '2025-09-29T13:00:00+00:00',
            score: 87.5,
        );

        self::assertSame('app-123', $dto->id);
        self::assertSame('Ada Lovelace', $dto->fullName);
        self::assertSame('ada@example.com', $dto->email);
        self::assertSame('Engineer', $dto->position);
        self::assertSame('PENDING', $dto->status);
        self::assertSame('2025-09-29T13:00:00+00:00', $dto->appliedAt);
        self::assertSame(87.5, $dto->score);
    }

    public function testScoreIsNullable(): void
    {
        $dto = new ApplicationDTO(
            id: 'app-456',
            fullName: 'Grace Hopper',
            email: 'grace@example.com',
            position: 'Manager',
            status: 'REVIEWED',
            appliedAt: '2025-09-28T10:00:00+00:00',
        );

        self::assertNull($dto->score);
    }
}
