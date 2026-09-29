<?php

declare(strict_types=1);

namespace App\Tests;

use App\Application\Domain\Model\JobApplication;
use App\Application\Infrastructure\Persistence\DoctrineJobApplicationRepository;
use App\Tests\Helpers\Mother\JobApplicationMother;
use Doctrine\ORM\EntityManagerInterface;

trait DatabaseTestTrait
{
    protected function entityManager(): EntityManagerInterface
    {
        if (isset($this->em) && $this->em instanceof EntityManagerInterface) {
            return $this->em;
        }

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        return $em;
    }

    protected function doctrineJobApplicationRepository(): DoctrineJobApplicationRepository
    {
        return new DoctrineJobApplicationRepository($this->entityManager());
    }

    protected function saveInDatabase(object ...$entities): void
    {
        $em = $this->entityManager();
        foreach ($entities as $entity) {
            $em->persist($entity);
        }
        $em->flush();
    }

    protected function givenApplication(
        string $name = 'Test User',
        string $email = 'test@example.com',
        string $position = 'Developer',
    ): JobApplication {
        $application = JobApplicationMother::builder()
            ->withName($name)
            ->withEmail($email)
            ->withPosition($position)
            ->build();

        $this->doctrineJobApplicationRepository()->save($application);

        return $application;
    }
}
