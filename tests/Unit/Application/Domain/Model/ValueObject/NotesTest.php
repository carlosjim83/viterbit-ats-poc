<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Domain\Model\ValueObject;

use App\Application\Domain\Model\ValueObject\Notes;
use PHPUnit\Framework\TestCase;

final class NotesTest extends TestCase
{
    public function testAcceptsAnyString(): void
    {
        $notes = new Notes('Some notes');

        self::assertSame('Some notes', $notes->value);
    }

    public function testAcceptsEmptyString(): void
    {
        $notes = new Notes('');

        self::assertSame('', $notes->value);
    }
}
