<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\Web;

use App\Application\Application\Query\GetApplicationDetail\GetApplicationDetail;
use Ecotone\Modelling\QueryBus;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

final readonly class ApplicationDetailController
{
    public function __construct(
        private QueryBus $queryBus,
        private Environment $twig,
    ) {
    }

    #[Route('/applications/{id}', name: 'application_detail', methods: ['GET'])]
    public function __invoke(string $id): Response
    {
        $dto = $this->queryBus->sendWithRouting(
            'application.detail',
            new GetApplicationDetail($id),
        );

        if (null === $dto) {
            throw new NotFoundHttpException('Application not found.');
        }

        return new Response($this->twig->render('application/detail.html.twig', [
            'application' => $dto,
        ]));
    }
}
