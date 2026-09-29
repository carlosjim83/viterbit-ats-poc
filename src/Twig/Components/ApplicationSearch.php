<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Application\Application\Query\ListApplications;
use Ecotone\Modelling\QueryBus;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ApplicationSearch
{
    use DefaultActionTrait;
    use ComponentToolsTrait;

    #[LiveProp(writable: true)]
    public string $status = '';

    #[LiveProp(writable: true)]
    public string $position = '';

    #[LiveProp(writable: true)]
    public string $search = '';

    public function __construct(
        private readonly QueryBus $queryBus,
    ) {
    }

    /**
     * @return list<mixed>
     */
    public function getApplications(): array
    {
        /** @var list<mixed> $applications */
        $applications = $this->queryBus->sendWithRouting(
            'application.list',
            new ListApplications(
                status: '' !== $this->status ? $this->status : null,
                position: '' !== $this->position ? $this->position : null,
                search: '' !== $this->search ? $this->search : null,
            )
        );

        return $applications;
    }
}
