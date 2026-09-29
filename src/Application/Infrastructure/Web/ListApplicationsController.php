<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\Web;

use App\Application\Application\Query\ListApplications;
use Ecotone\Modelling\QueryBus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

final readonly class ListApplicationsController
{
    public function __construct(
        private QueryBus $queryBus,
        private Environment $twig,
    ) {
    }

    #[Route('/applications', name: 'applications_list', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $status = (string) $request->query->get('status', '');
        $position = (string) $request->query->get('position', '');
        $search = (string) $request->query->get('search', '');

        /** @var list<array> $applications */
        $applications = $this->queryBus->sendWithRouting(
            'application.list',
            new ListApplications(
                status: '' !== $status ? $status : null,
                position: '' !== $position ? $position : null,
                search: '' !== $search ? $search : null,
            )
        );

        return new Response($this->twig->render('application/list.html.twig', [
            'applications' => $applications,
            'filters' => [
                'status' => $status,
                'position' => $position,
                'search' => $search,
            ],
        ]));
    }
}
