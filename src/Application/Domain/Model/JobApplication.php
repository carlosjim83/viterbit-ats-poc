<?php

declare(strict_types=1);

namespace App\Application\Domain\Model;

use App\Application\Domain\Event\ApplicationSubmitted;
use App\Application\Domain\Model\ValueObject\ApplicationId;
use App\Application\Domain\Model\ValueObject\CVText;
use App\Application\Domain\Model\ValueObject\Email;
use App\Application\Domain\Model\ValueObject\FullName;
use App\Application\Domain\Model\ValueObject\Notes;
use App\Application\Domain\Model\ValueObject\Phone;
use App\Application\Domain\Model\ValueObject\Position;
use App\Application\Domain\Model\ValueObject\Status;

final class JobApplication
{
    /** @var list<object> */
    private array $recordedEvents = [];

    private function __construct(
        public readonly ApplicationId $id,
        public readonly FullName $fullName,
        public readonly Email $email,
        public readonly Phone $phone,
        public readonly Position $position,
        public readonly Notes $notes,
        public readonly CVText $cvText,
        public readonly Status $status,
        public readonly \DateTimeImmutable $appliedAt,
    ) {
    }

    public static function submit(
        FullName $fullName,
        Email $email,
        Phone $phone,
        Position $position,
        Notes $notes,
        CVText $cvText,
    ): self {
        $id = ApplicationId::generate();
        $status = Status::received();
        $appliedAt = new \DateTimeImmutable();

        $application = new self($id, $fullName, $email, $phone, $position, $notes, $cvText, $status, $appliedAt);
        $application->recordEvent(new ApplicationSubmitted($id, $appliedAt));

        return $application;
    }

    /** @return list<object> */
    public function events(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];

        return $events;
    }

    private function recordEvent(object $event): void
    {
        $this->recordedEvents[] = $event;
    }
}
