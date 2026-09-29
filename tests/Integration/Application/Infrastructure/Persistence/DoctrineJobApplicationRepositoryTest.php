<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\Infrastructure\Persistence;

use App\Application\Domain\Model\JobApplication;
use App\Application\Domain\Model\ValueObject\CVText;
use App\Application\Domain\Model\ValueObject\Email;
use App\Application\Domain\Model\ValueObject\FullName;
use App\Application\Domain\Model\ValueObject\Notes;
use App\Application\Domain\Model\ValueObject\Phone;
use App\Application\Domain\Model\ValueObject\Position;
use App\Application\Infrastructure\Persistence\DoctrineJobApplicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineJobApplicationRepositoryTest extends KernelTestCase
{
    private DoctrineJobApplicationRepository $repository;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $this->em = $em;
        $this->repository = new DoctrineJobApplicationRepository($this->em);

        $conn = $this->em->getConnection();
        $conn->executeStatement('DELETE FROM job_applications');
    }

    public function testSaveAndFindById(): void
    {
        $application = $this->createApplication();
        $this->repository->save($application);

        $found = $this->repository->findById($application->id);

        self::assertNotNull($found);
        self::assertTrue($application->id->equals($found->id));
        self::assertSame($application->fullName->value, $found->fullName->value);
    }

    public function testFindByIdReturnsNullForUnknown(): void
    {
        $id = \App\Application\Domain\Model\ValueObject\ApplicationId::fromString(
            '550e8400-e29b-41d4-a716-446655440000'
        );

        $found = $this->repository->findById($id);

        self::assertNull($found);
    }

    public function testFindAllReturnsSavedApplications(): void
    {
        $app1 = $this->createApplication();
        $app2 = $this->createApplication();
        $this->repository->save($app1);
        $this->repository->save($app2);

        $all = $this->repository->findAll();

        self::assertCount(2, $all);
    }

    public function testFindByCriteriaFiltersByPosition(): void
    {
        $app1 = $this->createApplication('Alice', 'alice@example.com', 'Engineer');
        $app2 = $this->createApplication('Bob', 'bob@example.com', 'Manager');
        $this->repository->save($app1);
        $this->repository->save($app2);

        $filtered = $this->repository->findByCriteria(['position' => 'Engineer']);

        self::assertCount(1, $filtered);
        self::assertSame('Alice', $filtered[0]->fullName->value);
    }

    public function testFindByCriteriaSearchesByName(): void
    {
        $app1 = $this->createApplication('Alice Smith', 'alice@example.com');
        $app2 = $this->createApplication('Bob Jones', 'bob@example.com');
        $this->repository->save($app1);
        $this->repository->save($app2);

        $filtered = $this->repository->findByCriteria(['search' => 'Alice']);

        self::assertCount(1, $filtered);
        self::assertSame('Alice Smith', $filtered[0]->fullName->value);
    }

    public function testFindByCriteriaSearchesByEmail(): void
    {
        $app1 = $this->createApplication('Alice', 'alice@example.com');
        $app2 = $this->createApplication('Bob', 'bob@example.com');
        $this->repository->save($app1);
        $this->repository->save($app2);

        $filtered = $this->repository->findByCriteria(['search' => 'bob@example.com']);

        self::assertCount(1, $filtered);
        self::assertSame('Bob', $filtered[0]->fullName->value);
    }

    public function testFindByCriteriaSearchIsCaseInsensitive(): void
    {
        $app1 = $this->createApplication('Alice', 'alice@example.com');
        $this->repository->save($app1);

        $filtered = $this->repository->findByCriteria(['search' => 'ALICE']);

        self::assertCount(1, $filtered);
    }

    public function testFindByCriteriaReturnsNewestFirst(): void
    {
        $app1 = $this->createApplication('First', 'first@example.com');
        $this->repository->save($app1);

        // Sleep to ensure different timestamps (PostgreSQL timestamp precision is seconds)
        sleep(1);

        $app2 = $this->createApplication('Second', 'second@example.com');
        $this->repository->save($app2);

        $filtered = $this->repository->findByCriteria([]);

        self::assertCount(2, $filtered);
        self::assertSame('Second', $filtered[0]->fullName->value);
        self::assertSame('First', $filtered[1]->fullName->value);
    }

    private function createApplication(string $name = 'Test User', string $email = 'test@example.com', string $position = 'Developer'): JobApplication
    {
        return JobApplication::submit(
            new FullName($name),
            new Email($email),
            new Phone('+1234567890'),
            new Position($position),
            new Notes(''),
            new CVText('Some experience'),
        );
    }
}
