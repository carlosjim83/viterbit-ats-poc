<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\Application\Handler;

use App\Application\Application\Handler\ListApplicationsHandler;
use App\Application\Application\Query\ListApplications;
use App\Application\Infrastructure\Persistence\DoctrineJobApplicationRepository;
use App\Tests\Helpers\Mother\JobApplicationMother;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ListApplicationsHandlerTest extends KernelTestCase
{
    private ListApplicationsHandler $handler;
    private DoctrineJobApplicationRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $this->repository = new DoctrineJobApplicationRepository($em);
        $this->handler = new ListApplicationsHandler($this->repository);

        $conn = $em->getConnection();
        $conn->executeStatement('DELETE FROM job_applications');
    }

    public function testItReturnsApplicationDTOsFromDatabase(): void
    {
        $app = $this->createApplication('Ada Lovelace', 'ada@example.com', 'Engineer');
        $this->repository->save($app);

        $result = $this->handler->handle(new ListApplications());

        self::assertCount(1, $result);
        self::assertSame('Ada Lovelace', $result[0]->fullName);
        self::assertSame('Engineer', $result[0]->position);
    }

    public function testItFiltersByPosition(): void
    {
        $this->repository->save($this->createApplication('Alice', 'alice@example.com', 'Engineer'));
        $this->repository->save($this->createApplication('Bob', 'bob@example.com', 'Manager'));

        $result = $this->handler->handle(new ListApplications(position: 'Engineer'));

        self::assertCount(1, $result);
        self::assertSame('Alice', $result[0]->fullName);
    }

    public function testItSearchesByName(): void
    {
        $this->repository->save($this->createApplication('Alice Smith', 'alice@example.com'));
        $this->repository->save($this->createApplication('Bob Jones', 'bob@example.com'));

        $result = $this->handler->handle(new ListApplications(search: 'Alice'));

        self::assertCount(1, $result);
        self::assertSame('Alice Smith', $result[0]->fullName);
    }

    public function testItReturnsNewestFirst(): void
    {
        $this->repository->save($this->createApplication('First', 'first@example.com'));
        sleep(1);
        $this->repository->save($this->createApplication('Second', 'second@example.com'));

        $result = $this->handler->handle(new ListApplications());

        self::assertCount(2, $result);
        self::assertSame('Second', $result[0]->fullName);
        self::assertSame('First', $result[1]->fullName);
    }

    private function createApplication(string $name = 'Test User', string $email = 'test@example.com', string $position = 'Developer'): \App\Application\Domain\Model\JobApplication
    {
        return JobApplicationMother::builder()
            ->withName($name)
            ->withEmail($email)
            ->withPosition($position)
            ->build();
    }
}
