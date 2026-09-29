<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\Persistence;

use App\Application\Domain\Model\JobApplication;
use App\Application\Domain\Model\ValueObject\ApplicationId;
use App\Application\Domain\Model\ValueObject\CVText;
use App\Application\Domain\Model\ValueObject\Email;
use App\Application\Domain\Model\ValueObject\FullName;
use App\Application\Domain\Model\ValueObject\Position;
use App\Application\Domain\Model\ValueObject\Status;
use App\Application\Domain\Repository\JobApplicationRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineJobApplicationRepository implements JobApplicationRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(JobApplication $application): void
    {
        $entity = $this->entityManager->find(
            DoctrineJobApplication::class,
            (string) $application->id,
        );

        if (null !== $entity) {
            $entity->setFullName($application->fullName->value);
            $entity->setEmail($application->email->value);
            $entity->setPosition($application->position->value);
            $entity->setCvText($application->cvText->value);
            $entity->setStatus($application->status->value);
            $entity->setAppliedAt($application->appliedAt);
        } else {
            $entity = new DoctrineJobApplication(
                (string) $application->id,
                $application->fullName->value,
                $application->email->value,
                $application->position->value,
                $application->cvText->value,
                $application->status->value,
                $application->appliedAt,
            );
            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();
    }

    public function findById(ApplicationId $id): ?JobApplication
    {
        $entity = $this->entityManager->find(DoctrineJobApplication::class, (string) $id);

        if (null === $entity) {
            return null;
        }

        return $this->toAggregate($entity);
    }

    public function findAll(): array
    {
        $entities = $this->entityManager->getRepository(DoctrineJobApplication::class)->findAll();

        $applications = [];
        foreach ($entities as $entity) {
            $applications[] = $this->toAggregate($entity);
        }

        return $applications;
    }

    private function toAggregate(DoctrineJobApplication $entity): JobApplication
    {
        // Use reflection to reconstruct the aggregate from the entity
        $reflection = new \ReflectionClass(JobApplication::class);
        $instance = $reflection->newInstanceWithoutConstructor();

        $idProp = $reflection->getProperty('id');
        $idProp->setAccessible(true);
        $idProp->setValue($instance, ApplicationId::fromString($entity->getId()));

        $fullNameProp = $reflection->getProperty('fullName');
        $fullNameProp->setAccessible(true);
        $fullNameProp->setValue($instance, new FullName($entity->getFullName()));

        $emailProp = $reflection->getProperty('email');
        $emailProp->setAccessible(true);
        $emailProp->setValue($instance, new Email($entity->getEmail()));

        $positionProp = $reflection->getProperty('position');
        $positionProp->setAccessible(true);
        $positionProp->setValue($instance, new Position($entity->getPosition()));

        $cvTextProp = $reflection->getProperty('cvText');
        $cvTextProp->setAccessible(true);
        $cvTextProp->setValue($instance, new CVText($entity->getCvText()));

        $statusProp = $reflection->getProperty('status');
        $statusProp->setAccessible(true);
        $statusProp->setValue($instance, Status::fromString($entity->getStatus()));

        $appliedAtProp = $reflection->getProperty('appliedAt');
        $appliedAtProp->setAccessible(true);
        $appliedAtProp->setValue($instance, $entity->getAppliedAt());

        return $instance;
    }
}
