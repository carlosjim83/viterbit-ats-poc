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

    private function createApplication(): JobApplication
    {
        return JobApplication::submit(
            new FullName('Test User'),
            new Email('test@example.com'),
            new Phone('+1234567890'),
            new Position('Developer'),
            new Notes(''),
            new CVText('Some experience'),
        );
    }
}
