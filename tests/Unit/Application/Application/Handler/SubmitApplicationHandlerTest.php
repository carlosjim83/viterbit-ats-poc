<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Application\Handler;

use App\Application\Application\Command\SubmitApplication\SubmitApplication;
use App\Application\Application\Command\SubmitApplication\SubmitApplicationHandler;
use App\Application\Domain\Event\ApplicationSubmitted;
use App\Application\Domain\Event\EnrichmentRequested;
use App\Application\Domain\Model\JobApplication;
use App\Application\Domain\Model\ValueObject\ApplicationId;
use App\Application\Domain\Model\ValueObject\Email;
use App\Application\Domain\Repository\JobApplicationRepository;
use App\Shared\Domain\EventBus;
use PHPUnit\Framework\TestCase;

final class SubmitApplicationHandlerTest extends TestCase
{
    public function testHandleCreatesAndSavesApplication(): void
    {
        $repository = $this->createMock(JobApplicationRepository::class);
        $eventBus = $this->createMock(EventBus::class);

        $repository->method('findByEmail')->willReturn(null);

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

    public function testHandleRejectsDuplicateEmail(): void
    {
        $existing = JobApplication::submit(
            new \App\Application\Domain\Model\ValueObject\FullName('Existing'),
            new Email('duplicate@example.com'),
            new \App\Application\Domain\Model\ValueObject\Phone('+1111111111'),
            new \App\Application\Domain\Model\ValueObject\Position('Dev'),
            new \App\Application\Domain\Model\ValueObject\Notes(''),
            new \App\Application\Domain\Model\ValueObject\CVText('Existing CV'),
        );

        $repository = $this->createMock(JobApplicationRepository::class);
        $repository->method('findByEmail')
            ->with(self::callback(static fn (Email $email): bool => 'duplicate@example.com' === $email->value))
            ->willReturn($existing);

        $eventBus = $this->createMock(EventBus::class);
        $eventBus->expects(self::never())->method('publish');

        $handler = new SubmitApplicationHandler($repository, $eventBus);
        $command = new SubmitApplication(
            'Test User',
            'duplicate@example.com',
            '+1234567890',
            'Developer',
            'Some notes',
            'Some CV text',
        );

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('An application with this email already exists.');

        $handler->handle($command);
    }
}
