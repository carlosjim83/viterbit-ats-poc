<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\Web;

use App\Application\Application\Command\SubmitApplication\SubmitApplication;
use App\Application\Domain\Exception\DuplicateEmailException;
use App\Application\Infrastructure\Web\Form\ApplyType;
use Ecotone\Modelling\CommandBus;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

final readonly class ApplyController
{
    public function __construct(
        private CommandBus $commandBus,
        private FormFactoryInterface $formFactory,
        private Environment $twig,
    ) {
    }

    #[Route('/apply', name: 'apply', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $form = $this->formFactory->create(ApplyType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array<string, string> $data */
            $data = $form->getData();

            try {
                $command = new SubmitApplication(
                    $data['fullName'],
                    $data['email'],
                    $data['phone'],
                    $data['position'],
                    $data['notes'] ?? '',
                    $data['cvText'],
                );

                $this->commandBus->sendWithRouting('application.submit', $command);

                return new Response(
                    $this->twig->render('application/apply_success.html.twig'),
                    Response::HTTP_OK,
                );
            } catch (DuplicateEmailException $e) {
                $form->get('email')->addError(new \Symfony\Component\Form\FormError('An application with this email already exists.'));
            }
        }

        return new Response(
            $this->twig->render('application/apply.html.twig', [
                'form' => $form->createView(),
            ]),
            Response::HTTP_OK,
        );
    }
}
