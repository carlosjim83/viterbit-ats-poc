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
use App\Shared\Domain\Model\WithEvents;

final class JobApplication
{
    use WithEvents;

    public ?string $summary = null;
    public ?int $score = null;

    private function __construct(
        public readonly ApplicationId $id,
        public readonly FullName $fullName,
        public readonly Email $email,
        public readonly Phone $phone,
        public readonly Position $position,
        public readonly Notes $notes,
        public readonly CVText $cvText,
        public Status $status,
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

    public static function fromPersistence(
        ApplicationId $id,
        FullName $fullName,
        Email $email,
        Phone $phone,
        Position $position,
        Notes $notes,
        CVText $cvText,
        Status $status,
        \DateTimeImmutable $appliedAt,
    ): self {
        return new self($id, $fullName, $email, $phone, $position, $notes, $cvText, $status, $appliedAt);
    }

    public function requestEnrichment(): void
    {
        if ('received' !== $this->status->value) {
            throw new \DomainException('Enrichment can only be requested for applications in received status.');
        }

        $this->status = Status::enriching();
    }

    public function completeEnrichment(string $summary, int $score): void
    {
        if ('enriching' !== $this->status->value) {
            throw new \DomainException('Enrichment can only be completed for applications in enriching status.');
        }

        $this->summary = $summary;
        $this->score = $score;
        $this->status = Status::enriched();
    }
}
