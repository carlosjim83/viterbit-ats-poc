<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Application\Handler;

use App\Application\Application\Command\SubmitApplication\SubmitApplication;
use App\Application\Application\Command\SubmitApplication\SubmitApplicationHandler;
use App\Application\Application\EventBus;
use App\Application\Domain\Event\ApplicationSubmitted;
use App\Application\Domain\Event\EnrichmentRequested;
use App\Application\Domain\Model\JobApplication;
use App\Application\Domain\Model\ValueObject\ApplicationId;
use App\Application\Domain\Repository\JobApplicationRepository;
use PHPUnit\Framework\TestCase;

final class SubmitApplicationHandlerTest extends TestCase
{
    public function testHandleCreatesAndSavesApplication(): void
    {
        $repository = $this->createMock(JobApplicationRepository::class);
        $eventBus = $this->createMock(EventBus::class);

        $repository->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (JobApplication $app): bool {
                return 'Test User' === $app->fullName->value
                    && 'test@example.com' === $app->email->value
                    && '+1234567890' === $app->phone->value
                    && 'Developer' === $app->position->value
                    && 'Some notes' === $app->notes->value
                    && 'Some CV text' === $app->cvText->value
                    && 'received' === $app->status->value;
            }));

        $publishedEvents = [];
        $eventBus->expects(self::exactly(2))
            ->method('publish')
            ->willReturnCallback(static function ($event) use (&$publishedEvents): void {
                $publishedEvents[] = $event;
            });

        $handler = new SubmitApplicationHandler($repository, $eventBus);
        $command = new SubmitApplication(
            'Test User',
            'test@example.com',
            '+1234567890',
            'Developer',
            'Some notes',
            'Some CV text',
        );

        $id = $handler->handle($command);

        self::assertInstanceOf(ApplicationId::class, $id);
        self::assertCount(2, $publishedEvents);
        self::assertInstanceOf(ApplicationSubmitted::class, $publishedEvents[0]);
        self::assertInstanceOf(EnrichmentRequested::class, $publishedEvents[1]);
        self::assertTrue($id->equals($publishedEvents[1]->applicationId));
    }
}
