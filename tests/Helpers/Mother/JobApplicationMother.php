<?php

declare(strict_types=1);

namespace App\Tests\Helpers\Mother;

use App\Application\Domain\Model\JobApplication;
use App\Application\Domain\Model\ValueObject\CVText;
use App\Application\Domain\Model\ValueObject\Email;
use App\Application\Domain\Model\ValueObject\FullName;
use App\Application\Domain\Model\ValueObject\Notes;
use App\Application\Domain\Model\ValueObject\Phone;
use App\Application\Domain\Model\ValueObject\Position;

final class JobApplicationMother
{
    private string $name = 'Ada Lovelace';
    private string $email = 'ada@example.com';
    private string $phone = '+441111111111';
    private string $position = 'Engineering Manager';
    private string $notes = 'Remote only';
    private string $cvText = 'Pioneer of computer science.';

    public static function builder(): self
    {
        return new self();
    }

    public static function create(): JobApplication
    {
        return self::builder()->create();
    }

    public function withName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function withEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function withPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function withPosition(string $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function withNotes(string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    public function withCvText(string $cvText): self
    {
        $this->cvText = $cvText;

        return $this;
    }

    public function build(): JobApplication
    {
        return JobApplication::submit(
            new FullName($this->name),
            new Email($this->email),
            new Phone($this->phone),
            new Position($this->position),
            new Notes($this->notes),
            new CVText($this->cvText),
        );
    }
}
