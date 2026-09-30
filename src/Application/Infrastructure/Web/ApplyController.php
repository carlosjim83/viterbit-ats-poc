<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\Web;

use App\Application\Application\Command\SubmitApplication\SubmitApplication;
use Ecotone\Modelling\CommandBus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

final readonly class ApplyController
{
    public function __construct(
        private CommandBus $commandBus,
        private Environment $twig,
    ) {
    }

    #[Route('/apply', name: 'apply', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $errors = [];
        $data = [
            'fullName' => '',
            'email' => '',
            'phone' => '',
            'position' => '',
            'notes' => '',
            'cvText' => '',
        ];

        if ($request->isMethod('POST')) {
            $data = [
                'fullName' => (string) $request->request->get('fullName', ''),
                'email' => (string) $request->request->get('email', ''),
                'phone' => (string) $request->request->get('phone', ''),
                'position' => (string) $request->request->get('position', ''),
                'notes' => (string) $request->request->get('notes', ''),
                'cvText' => (string) $request->request->get('cvText', ''),
            ];

            if ('' === trim($data['fullName'])) {
                $errors['fullName'] = 'Full name is required.';
            }
            if ('' === trim($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'A valid email is required.';
            }
            if ('' === trim($data['phone'])) {
                $errors['phone'] = 'Phone is required.';
            }
            if ('' === trim($data['position'])) {
                $errors['position'] = 'Position is required.';
            }
            if ('' === trim($data['cvText'])) {
                $errors['cvText'] = 'CV text is required.';
            }

            if ([] === $errors) {
                try {
                    $command = new SubmitApplication(
                        $data['fullName'],
                        $data['email'],
                        $data['phone'],
                        $data['position'],
                        $data['notes'],
                        $data['cvText'],
                    );

                    $this->commandBus->sendWithRouting('application.submit', $command);

                    return new Response(
                        $this->twig->render('application/apply_success.html.twig'),
                        Response::HTTP_OK,
                    );
                } catch (\DomainException $e) {
                    $errors['email'] = $e->getMessage();
                }
            }
        }

        return new Response(
            $this->twig->render('application/apply.html.twig', [
                'data' => $data,
                'errors' => $errors,
            ]),
            Response::HTTP_OK,
        );
    }
}
