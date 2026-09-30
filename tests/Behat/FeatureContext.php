<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Hook\BeforeScenario;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Doctrine\ORM\EntityManagerInterface;
use Ecotone\Messaging\Config\ConfiguredMessagingSystem;
use Ecotone\Messaging\Endpoint\ExecutionPollingMetadata;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

final class FeatureContext implements Context
{
    private KernelInterface $kernel;
    private EntityManagerInterface $em;
    private ?Response $lastResponse = null;

    public function __construct(KernelInterface $kernel, EntityManagerInterface $em)
    {
        $this->kernel = $kernel;
        $this->em = $em;
    }

    #[BeforeScenario]
    public function clearDatabase(): void
    {
        $conn = $this->em->getConnection();
        $conn->executeStatement('DELETE FROM job_applications');

        try {
            $conn->executeStatement('DELETE FROM messenger_messages');
        } catch (\Doctrine\DBAL\Exception\TableNotFoundException) {
            // Table may not exist yet if messenger auto_setup is disabled
        }
    }

    #[Given('I am on :path')]
    public function iAmOn(string $path): void
    {
        $this->lastResponse = $this->kernel->handle(Request::create($path));
    }

    #[When('I submit an application with:')]
    public function iSubmitAnApplicationWith(TableNode $table): void
    {
        /** @var array<string, string> $data */
        $data = $table->getRowsHash();

        $this->lastResponse = $this->kernel->handle(Request::create(
            '/apply',
            'POST',
            [
                'fullName' => $data['fullName'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'position' => $data['position'],
                'notes' => $data['notes'] ?? '',
                'cvText' => $data['cvText'],
            ]
        ));
    }

    #[When('the enrichment process runs')]
    public function theEnrichmentProcessRuns(): void
    {
        $container = $this->kernel->getContainer();
        /** @var ConfiguredMessagingSystem $messaging */
        $messaging = $container->get(ConfiguredMessagingSystem::class);
        $messaging->run('enrichment', ExecutionPollingMetadata::createWithFinishWhenNoMessages());
    }

    #[Then('I should see :text')]
    public function iShouldSee(string $text): void
    {
        $response = (string) $this->getLastResponse()->getContent();
        if (!str_contains($response, $text)) {
            throw new \RuntimeException(sprintf('Expected to see "%s" but did not.', $text));
        }
    }

    #[Then('the response status code should be :code')]
    public function theResponseStatusCodeShouldBe(int $code): void
    {
        $actual = $this->getLastResponse()->getStatusCode();
        if ($actual !== $code) {
            throw new \RuntimeException(sprintf('Expected status %d, got %d.', $code, $actual));
        }
    }

    #[When('I am on "/applications/" followed by the application id for :email')]
    public function iAmOnApplicationDetailForEmail(string $email): void
    {
        $conn = $this->em->getConnection();
        /** @var string|false $id */
        $id = $conn->fetchOne('SELECT id FROM job_applications WHERE email = ?', [$email]);
        if (!is_string($id)) {
            throw new \RuntimeException(sprintf('No application found for email %s.', $email));
        }
        $this->lastResponse = $this->kernel->handle(Request::create('/applications/'.$id));
    }

    #[Then('I should see a summary')]
    public function iShouldSeeASummary(): void
    {
        $response = (string) $this->getLastResponse()->getContent();
        if (!str_contains($response, 'data-testid="application-summary"')) {
            throw new \RuntimeException('Summary element not found.');
        }
        if (preg_match('/data-testid="application-summary">\s*<\/p>/', $response)) {
            throw new \RuntimeException('Summary is empty.');
        }
    }

    #[Then('I should see a score between :min and :max')]
    public function iShouldSeeAScoreBetween(int $min, int $max): void
    {
        $response = (string) $this->getLastResponse()->getContent();
        if (!preg_match('/data-testid="application-score"[^>]*>(?:\s*<[^>]+>\s*)*(\d+)/', $response, $matches)) {
            throw new \RuntimeException('Score element not found.');
        }
        $score = (int) $matches[1];
        if ($score < $min || $score > $max) {
            throw new \RuntimeException(sprintf('Score %d not in range %d-%d.', $score, $min, $max));
        }
    }

    private function getLastResponse(): Response
    {
        if (null === $this->lastResponse) {
            throw new \RuntimeException('No response available. Make a request first.');
        }

        return $this->lastResponse;
    }
}
