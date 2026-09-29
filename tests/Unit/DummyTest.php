<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class DummyTest extends TestCase
{
    public function testPhpunitIsConfigured(): void
    {
        $value = false !== getenv('APP_ENV');

        self::assertTrue($value);
    }
}
