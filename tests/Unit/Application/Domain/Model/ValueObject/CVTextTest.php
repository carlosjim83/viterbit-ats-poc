<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Domain\Model\ValueObject;

use App\Application\Domain\Model\ValueObject\CVText;
use PHPUnit\Framework\TestCase;

final class CVTextTest extends TestCase
{
    public function testAcceptsNonEmptyText(): void
    {
        $cv = new CVText('Experienced developer with 5 years...');

        self::assertSame('Experienced developer with 5 years...', $cv->value);
    }

    public function testRejectsEmptyText(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CVText('');
    }
}
