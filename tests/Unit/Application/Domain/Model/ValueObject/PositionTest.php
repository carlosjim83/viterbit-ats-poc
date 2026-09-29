<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Domain\Model\ValueObject;

use App\Application\Domain\Model\ValueObject\Position;
use PHPUnit\Framework\TestCase;

final class PositionTest extends TestCase
{
    public function testAcceptsNonEmptyPosition(): void
    {
        $position = new Position('Senior PHP Developer');

        self::assertSame('Senior PHP Developer', $position->value);
    }

    public function testRejectsEmptyPosition(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Position('');
    }
}
