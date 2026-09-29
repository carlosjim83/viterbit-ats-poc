<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Domain\Model;

use App\Application\Domain\Event\ApplicationSubmitted;
use App\Application\Domain\Model\JobApplication;
use App\Application\Domain\Model\ValueObject\CVText;
use App\Application\Domain\Model\ValueObject\Email;
use App\Application\Domain\Model\ValueObject\FullName;
use App\Application\Domain\Model\ValueObject\Position;
use PHPUnit\Framework\TestCase;

final class JobApplicationTest extends TestCase
{
    public function testSubmitCreatesApplicationWithCorrectData(): void
    {
        $application = JobApplication::submit(
            new FullName('John Doe'),
            new Email('john@example.com'),
            new Position('PHP Developer'),
            new CVText('5 years of PHP experience'),
        );

        self::assertSame('John Doe', $application->fullName->value);
        self::assertSame('john@example.com', $application->email->value);
        self::assertSame('PHP Developer', $application->position->value);
        self::assertSame('5 years of PHP experience', $application->cvText->value);
    }

    public function testSubmitGeneratesUniqueId(): void
    {
        $app1 = $this->submitApplication();
        $app2 = $this->submitApplication();

        self::assertFalse($app1->id->equals($app2->id));
    }

    public function testSubmitSetsReceivedStatus(): void
    {
        $application = $this->submitApplication();

        self::assertSame('received', $application->status->value);
    }

    public function testSubmitSetsAppliedAtTimestamp(): void
    {
        $before = new \DateTimeImmutable();
        $application = $this->submitApplication();
        $after = new \DateTimeImmutable();

        self::assertGreaterThanOrEqual($before, $application->appliedAt);
        self::assertLessThanOrEqual($after, $application->appliedAt);
    }

    public function testSubmitRecordsApplicationSubmittedEvent(): void
    {
        $application = $this->submitApplication();
        $events = $application->events();

        self::assertCount(1, $events);
        self::assertInstanceOf(ApplicationSubmitted::class, $events[0]);
        self::assertTrue($application->id->equals($events[0]->applicationId));
    }

    public function testEventsClearsRecordedEvents(): void
    {
        $application = $this->submitApplication();
        $application->events();
        $secondPull = $application->events();

        self::assertCount(0, $secondPull);
    }

    private function submitApplication(): JobApplication
    {
        return JobApplication::submit(
            new FullName('Jane Doe'),
            new Email('jane@example.com'),
            new Position('Senior Developer'),
            new CVText('Experienced developer'),
        );
    }
}
