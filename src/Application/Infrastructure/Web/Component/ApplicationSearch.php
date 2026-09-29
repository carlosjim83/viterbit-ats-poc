<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\Web\Component;

use App\Application\Application\Query\ListApplications\ApplicationDTO;
use App\Application\Application\Query\ListApplications\ListApplications;
use Ecotone\Modelling\QueryBus;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
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

    #[LiveProp(writable: true)]
    public string $sortBy = 'appliedAt';

    #[LiveProp(writable: true)]
    public string $sortDir = 'desc';

    public function __construct(
        private readonly QueryBus $queryBus,
    ) {
    }

    #[LiveAction]
    public function clear(): void
    {
        $this->status = '';
        $this->position = '';
        $this->search = '';
        $this->sortBy = 'appliedAt';
        $this->sortDir = 'desc';
    }

    #[LiveAction]
    public function sort(#[LiveArg] string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = 'asc' === $this->sortDir ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'asc';
        }
    }

    /**
     * @return list<mixed>
     */
    public function getApplications(): array
    {
        /** @var list<ApplicationDTO> $applications */
        $applications = $this->queryBus->sendWithRouting(
            'application.list',
            new ListApplications(
                status: '' !== $this->status ? $this->status : null,
                position: '' !== $this->position ? $this->position : null,
                search: '' !== $this->search ? $this->search : null,
            )
        );

        usort($applications, function (ApplicationDTO $a, ApplicationDTO $b): int {
            $getter = $this->sortBy;

            $aVal = $a->$getter;
            $bVal = $b->$getter;

            if (null === $aVal && null === $bVal) {
                return 0;
            }

            if (null === $aVal) {
                return 'asc' === $this->sortDir ? 1 : -1;
            }

            if (null === $bVal) {
                return 'asc' === $this->sortDir ? -1 : 1;
            }

            if (is_numeric($aVal) && is_numeric($bVal)) {
                $aNum = (float) $aVal;
                $bNum = (float) $bVal;

                return 'asc' === $this->sortDir ? $aNum <=> $bNum : $bNum <=> $aNum;
            }

            $aStr = is_string($aVal) ? $aVal : (is_scalar($aVal) ? strval($aVal) : '');
            $bStr = is_string($bVal) ? $bVal : (is_scalar($bVal) ? strval($bVal) : '');
            $comparison = strcasecmp($aStr, $bStr);

            return 'asc' === $this->sortDir ? $comparison : -$comparison;
        });

        /** @var list<mixed> $result */
        $result = $applications;

        return $result;
    }
}
