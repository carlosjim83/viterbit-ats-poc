<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Domain;

use App\Application\Domain\LLMClientInterface;
use PHPUnit\Framework\TestCase;

final class LLMClientInterfaceTest extends TestCase
{
    public function testInterfaceExists(): void
    {
        self::assertTrue(interface_exists(LLMClientInterface::class));
    }

    public function testInterfaceHasEnrichMethod(): void
    {
        $reflection = new \ReflectionClass(LLMClientInterface::class);
        self::assertTrue($reflection->hasMethod('enrich'));

        $method = $reflection->getMethod('enrich');
        self::assertSame(2, $method->getNumberOfParameters());
    }
}
