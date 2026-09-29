<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Application\DTO;

use App\Application\Application\DTO\ApplicationDetailDTO;
use PHPUnit\Framework\TestCase;

final class ApplicationDetailDTOTest extends TestCase
{
    public function testItHoldsAllDetailFields(): void
    {
        $dto = new ApplicationDetailDTO(
            id: 'app-123',
            fullName: 'Ada Lovelace',
            email: 'ada@example.com',
            phone: '+441111111111',
            position: 'Engineering Manager',
            notes: 'Remote only',
            cvText: 'Pioneer of computer science.',
            status: 'received',
            appliedAt: '2025-09-29T13:00:00+00:00',
            summary: 'Strong candidate with extensive analytical experience.',
            score: 92,
        );

        self::assertSame('app-123', $dto->id);
        self::assertSame('Ada Lovelace', $dto->fullName);
        self::assertSame('ada@example.com', $dto->email);
        self::assertSame('+441111111111', $dto->phone);
        self::assertSame('Engineering Manager', $dto->position);
        self::assertSame('Remote only', $dto->notes);
        self::assertSame('Pioneer of computer science.', $dto->cvText);
        self::assertSame('received', $dto->status);
        self::assertSame('2025-09-29T13:00:00+00:00', $dto->appliedAt);
        self::assertSame('Strong candidate with extensive analytical experience.', $dto->summary);
        self::assertSame(92, $dto->score);
    }

    public function testSummaryAndScoreAreNullable(): void
    {
        $dto = new ApplicationDetailDTO(
            id: 'app-456',
            fullName: 'Grace Hopper',
            email: 'grace@example.com',
            phone: '+442222222222',
            position: 'Senior Developer',
            notes: '',
            cvText: 'Expert in COBOL.',
            status: 'enriching',
            appliedAt: '2025-09-28T10:00:00+00:00',
        );

        self::assertNull($dto->summary);
        self::assertNull($dto->score);
    }
}
