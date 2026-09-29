<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Domain\Model\ValueObject;

use App\Application\Domain\Model\ValueObject\Phone;
use PHPUnit\Framework\TestCase;

final class PhoneTest extends TestCase
{
    public function testValidPhoneIsAccepted(): void
    {
        $phone = new Phone('+1234567890');

        self::assertSame('+1234567890', $phone->value);
    }

    public function testEmptyPhoneThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Phone cannot be empty');

        new Phone('');
    }

    public function testWhitespaceOnlyPhoneThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Phone('   ');
    }
}
