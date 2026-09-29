<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\Web;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

final readonly class ListApplicationsController
{
    public function __construct(
        private Environment $twig,
    ) {
    }

    #[Route('/applications', name: 'applications_list', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        return new Response($this->twig->render('application/list.html.twig', [
            'status' => (string) $request->query->get('status', ''),
            'position' => (string) $request->query->get('position', ''),
            'search' => (string) $request->query->get('search', ''),
        ]));
    }
}
