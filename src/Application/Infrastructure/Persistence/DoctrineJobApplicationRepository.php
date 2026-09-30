<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\Persistence;

use App\Application\Domain\Model\JobApplication;
use App\Application\Domain\Model\ValueObject\ApplicationId;
use App\Application\Domain\Model\ValueObject\CVText;
use App\Application\Domain\Model\ValueObject\Email;
use App\Application\Domain\Model\ValueObject\FullName;
use App\Application\Domain\Model\ValueObject\Notes;
use App\Application\Domain\Model\ValueObject\Phone;
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
            $entity->setPhone($application->phone->value);
            $entity->setPosition($application->position->value);
            $entity->setNotes($application->notes->value);
            $entity->setCvText($application->cvText->value);
            $entity->setStatus($application->status->value);
            $entity->setSummary($application->summary);
            $entity->setScore($application->score);
            $entity->setAppliedAt($application->appliedAt);
        } else {
            $entity = new DoctrineJobApplication(
                (string) $application->id,
                $application->fullName->value,
                $application->email->value,
                $application->phone->value,
                $application->position->value,
                $application->notes->value,
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

    public function findByEmail(Email $email): ?JobApplication
    {
        $entity = $this->entityManager->getRepository(DoctrineJobApplication::class)
            ->findOneBy(['email' => $email->value]);

        if (null === $entity) {
            return null;
        }

        return $this->toAggregate($entity);
    }

    public function findAll(): array
    {
        $entities = $this->entityManager->getRepository(DoctrineJobApplication::class)
            ->findBy([], ['appliedAt' => 'DESC']);

        $applications = [];
        foreach ($entities as $entity) {
            $applications[] = $this->toAggregate($entity);
        }

        return $applications;
    }

    public function findByCriteria(array $criteria): array
    {
        $qb = $this->entityManager->getRepository(DoctrineJobApplication::class)
            ->createQueryBuilder('ja')
            ->orderBy('ja.appliedAt', 'DESC');

        if (isset($criteria['status']) && '' !== $criteria['status']) {
            $qb->andWhere('ja.status = :status')
                ->setParameter('status', $criteria['status']);
        }

        if (isset($criteria['position']) && '' !== $criteria['position']) {
            $qb->andWhere('LOWER(ja.position) LIKE LOWER(:position)')
                ->setParameter('position', '%'.$criteria['position'].'%');
        }

        if (isset($criteria['search']) && '' !== $criteria['search']) {
            $qb->andWhere(
                $qb->expr()->orX(
                    'LOWER(ja.fullName) LIKE LOWER(:search)',
                    'LOWER(ja.email) LIKE LOWER(:search)',
                )
            )->setParameter('search', '%'.$criteria['search'].'%');
        }

        /** @var list<DoctrineJobApplication> $entities */
        $entities = $qb->getQuery()->getResult();

        $applications = [];
        foreach ($entities as $entity) {
            $applications[] = $this->toAggregate($entity);
        }

        return $applications;
    }

    private function toAggregate(DoctrineJobApplication $entity): JobApplication
    {
        return JobApplication::fromPersistence(
            ApplicationId::fromString($entity->getId()),
            new FullName($entity->getFullName()),
            new Email($entity->getEmail()),
            new Phone($entity->getPhone()),
            new Position($entity->getPosition()),
            new Notes($entity->getNotes() ?? ''),
            new CVText($entity->getCvText()),
            Status::fromString($entity->getStatus()),
            $entity->getAppliedAt(),
            $entity->getSummary(),
            $entity->getScore(),
        );
    }
}
