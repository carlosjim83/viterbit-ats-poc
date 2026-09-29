<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\Infrastructure\Persistence;

use App\Application\Domain\Model\ValueObject\ApplicationId;
use App\Tests\DatabaseTestCase;

final class DoctrineJobApplicationRepositoryTest extends DatabaseTestCase
{
    public function testSaveAndFindById(): void
    {
        $application = $this->givenApplication();

        $found = $this->doctrineJobApplicationRepository()->findById($application->id);

        self::assertNotNull($found);
        self::assertTrue($application->id->equals($found->id));
        self::assertSame($application->fullName->value, $found->fullName->value);
    }

    public function testFindByIdReturnsNullForUnknown(): void
    {
        $id = ApplicationId::fromString('550e8400-e29b-41d4-a716-446655440000');

        $found = $this->doctrineJobApplicationRepository()->findById($id);

        self::assertNull($found);
    }

    public function testFindAllReturnsSavedApplications(): void
    {
        $this->givenApplication('User One', 'one@example.com');
        $this->givenApplication('User Two', 'two@example.com');

        $all = $this->doctrineJobApplicationRepository()->findAll();

        self::assertCount(2, $all);
    }

    public function testFindByCriteriaFiltersByPosition(): void
    {
        $this->givenApplication('Alice', 'alice@example.com', 'Engineer');
        $this->givenApplication('Bob', 'bob@example.com', 'Manager');

        $filtered = $this->doctrineJobApplicationRepository()->findByCriteria(['position' => 'Engineer']);

        self::assertCount(1, $filtered);
        self::assertSame('Alice', $filtered[0]->fullName->value);
    }

    public function testFindByCriteriaSearchesByName(): void
    {
        $this->givenApplication('Alice Smith', 'alice@example.com');
        $this->givenApplication('Bob Jones', 'bob@example.com');

        $filtered = $this->doctrineJobApplicationRepository()->findByCriteria(['search' => 'Alice']);

        self::assertCount(1, $filtered);
        self::assertSame('Alice Smith', $filtered[0]->fullName->value);
    }

    public function testFindByCriteriaSearchesByEmail(): void
    {
        $this->givenApplication('Alice', 'alice@example.com');
        $this->givenApplication('Bob', 'bob@example.com');

        $filtered = $this->doctrineJobApplicationRepository()->findByCriteria(['search' => 'bob@example.com']);

        self::assertCount(1, $filtered);
        self::assertSame('Bob', $filtered[0]->fullName->value);
    }

    public function testFindByCriteriaSearchIsCaseInsensitive(): void
    {
        $this->givenApplication('Alice', 'alice@example.com');

        $filtered = $this->doctrineJobApplicationRepository()->findByCriteria(['search' => 'ALICE']);

        self::assertCount(1, $filtered);
    }

    public function testFindByCriteriaReturnsNewestFirst(): void
    {
        $this->givenApplication('First', 'first@example.com');
        sleep(1);
        $this->givenApplication('Second', 'second@example.com');

        $filtered = $this->doctrineJobApplicationRepository()->findByCriteria([]);

        self::assertCount(2, $filtered);
        self::assertSame('Second', $filtered[0]->fullName->value);
        self::assertSame('First', $filtered[1]->fullName->value);
    }
}
