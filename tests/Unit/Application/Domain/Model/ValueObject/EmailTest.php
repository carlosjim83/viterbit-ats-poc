<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Domain\Model\ValueObject;

use App\Application\Domain\Model\ValueObject\Email;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    public function testAcceptsValidEmail(): void
    {
        $email = new Email('candidate@example.com');

        self::assertSame('candidate@example.com', $email->value);
    }

    public function testRejectsInvalidEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Email('not-an-email');
    }
}
