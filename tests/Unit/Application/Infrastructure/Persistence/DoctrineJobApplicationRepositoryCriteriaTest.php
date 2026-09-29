<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Infrastructure\Persistence;

use App\Application\Domain\Model\JobApplication;
use App\Application\Domain\Model\ValueObject\CVText;
use App\Application\Domain\Model\ValueObject\Email;
use App\Application\Domain\Model\ValueObject\FullName;
use App\Application\Domain\Model\ValueObject\Notes;
use App\Application\Domain\Model\ValueObject\Phone;
use App\Application\Domain\Model\ValueObject\Position;
use App\Application\Infrastructure\Persistence\DoctrineJobApplicationRepository;
use App\Tests\DatabaseTestCase;

final class DoctrineJobApplicationRepositoryCriteriaTest extends DatabaseTestCase
{
    public function testFindByCriteriaWithPartialPositionMatch(): void
    {
        $repository = new DoctrineJobApplicationRepository($this->entityManager());

        $app1 = JobApplication::submit(
            new FullName('Ada Lovelace'),
            new Email('ada@example.com'),
            new Phone('+441111111111'),
            new Position('Engineering Manager'),
            new Notes(''),
            new CVText('PHP expert'),
        );

        $app2 = JobApplication::submit(
            new FullName('Grace Hopper'),
            new Email('grace@example.com'),
            new Phone('+442222222222'),
            new Position('Senior Developer'),
            new Notes(''),
            new CVText('COBOL pioneer'),
        );

        $repository->save($app1);
        $repository->save($app2);

        // Partial match: "Engineer" should match "Engineering Manager"
        $results = $repository->findByCriteria(['position' => 'Engineer']);

        self::assertCount(1, $results);
        self::assertSame('Ada Lovelace', $results[0]->fullName->value);
    }

    public function testFindByCriteriaWithPositionIsCaseInsensitive(): void
    {
        $repository = new DoctrineJobApplicationRepository($this->entityManager());

        $app = JobApplication::submit(
            new FullName('Alan Turing'),
            new Email('alan@example.com'),
            new Phone('+443333333333'),
            new Position('AI Researcher'),
            new Notes(''),
            new CVText('ML expert'),
        );

        $repository->save($app);

        $results = $repository->findByCriteria(['position' => 'ai researcher']);

        self::assertCount(1, $results);
        self::assertSame('Alan Turing', $results[0]->fullName->value);
    }
}
