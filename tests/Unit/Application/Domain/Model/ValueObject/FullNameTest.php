<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Domain\Model\ValueObject;

use App\Application\Domain\Model\ValueObject\FullName;
use PHPUnit\Framework\TestCase;

final class FullNameTest extends TestCase
{
    public function testAcceptsNonEmptyName(): void
    {
        $name = new FullName('John Doe');

        self::assertSame('John Doe', $name->value);
    }

    public function testRejectsEmptyName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new FullName('');
    }

    public function testRejectsWhitespaceOnlyName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new FullName('   ');
    }
}
