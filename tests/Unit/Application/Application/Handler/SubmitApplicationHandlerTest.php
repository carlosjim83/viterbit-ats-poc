<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Application\Handler;

use App\Application\Application\Command\SubmitApplication;
use App\Application\Application\EventBus;
use App\Application\Application\Handler\SubmitApplicationHandler;
use App\Application\Domain\Event\ApplicationSubmitted;
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
                    && 'Developer' === $app->position->value
                    && 'Some CV text' === $app->cvText->value
                    && 'received' === $app->status->value;
            }));

        $eventBus->expects(self::once())
            ->method('publish')
            ->with(self::callback(static function ($event): bool {
                return $event instanceof ApplicationSubmitted;
            }));

        $handler = new SubmitApplicationHandler($repository, $eventBus);
        $command = new SubmitApplication(
            'Test User',
            'test@example.com',
            'Developer',
            'Some CV text',
        );

        $id = $handler->handle($command);

        self::assertInstanceOf(ApplicationId::class, $id);
    }
}
