<?php

declare(strict_types=1);

namespace App\Application\Application\Command\SubmitApplication;

use App\Application\Domain\Event\EnrichmentRequested;
use App\Application\Domain\Exception\DuplicateEmailException;
use App\Application\Domain\Model\JobApplication;
use App\Application\Domain\Model\ValueObject\ApplicationId;
use App\Application\Domain\Model\ValueObject\CVText;
use App\Application\Domain\Model\ValueObject\Email;
use App\Application\Domain\Model\ValueObject\FullName;
use App\Application\Domain\Model\ValueObject\Notes;
use App\Application\Domain\Model\ValueObject\Phone;
use App\Application\Domain\Model\ValueObject\Position;
use App\Application\Domain\Repository\JobApplicationRepository;
use App\Shared\Domain\EventBus;
use Ecotone\Modelling\Attribute\CommandHandler;

final readonly class SubmitApplicationHandler
{
    public function __construct(
        private JobApplicationRepository $repository,
        private EventBus $eventBus,
    ) {
    }

    #[CommandHandler('application.submit')]
    public function handle(SubmitApplication $command): ApplicationId
    {
        $email = new Email($command->email);

        if (null !== $this->repository->findByEmail($email)) {
            throw new DuplicateEmailException($command->email);
        }

        $application = JobApplication::submit(
            new FullName($command->fullName),
            $email,
            new Phone($command->phone),
            new Position($command->position),
            new Notes($command->notes),
            new CVText($command->cvText),
        );

        $this->repository->save($application);

        foreach ($application->events() as $event) {
            $this->eventBus->publish($event);
        }

        $this->eventBus->publish(new EnrichmentRequested($application->id));

        return $application->id;
    }
}
