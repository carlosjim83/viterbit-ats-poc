<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\Infrastructure\Web;

use App\Application\Infrastructure\Persistence\DoctrineJobApplicationRepository;
use App\Tests\Helpers\Mother\JobApplicationMother;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ListApplicationsControllerTest extends WebTestCase
{
    private function cleanDatabase(): void
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $conn = $em->getConnection();
        $conn->executeStatement('DELETE FROM job_applications');
    }

    public function testListPageLoadsWithFilters(): void
    {
        $client = static::createClient();
        $this->cleanDatabase();
        $client->request('GET', '/applications');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
        self::assertSelectorExists('select[name="status"]');
        self::assertSelectorExists('input[name="position"]');
        self::assertSelectorExists('input[name="search"]');
        self::assertSelectorTextContains('h1', 'Job Applications');
    }

    public function testListShowsApplications(): void
    {
        $client = static::createClient();
        $this->cleanDatabase();
        $this->seedApplication('Ada Lovelace', 'ada@example.com', 'Engineer');

        $client->request('GET', '/applications');

        self::assertResponseIsSuccessful();
        $html = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('Ada Lovelace', $html);
        self::assertStringContainsString('ada@example.com', $html);
        self::assertStringContainsString('Engineer', $html);
    }

    public function testListFiltersByPosition(): void
    {
        $client = static::createClient();
        $this->cleanDatabase();
        $this->seedApplication('Alice', 'alice@example.com', 'Engineer');
        $this->seedApplication('Bob', 'bob@example.com', 'Manager');

        $client->request('GET', '/applications?position=Engineer');

        self::assertResponseIsSuccessful();
        $html = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('Alice', $html);
        self::assertStringNotContainsString('Bob', $html);
    }

    public function testListFiltersBySearch(): void
    {
        $client = static::createClient();
        $this->cleanDatabase();
        $this->seedApplication('Alice Smith', 'alice@example.com', 'Engineer');
        $this->seedApplication('Bob Jones', 'bob@example.com', 'Manager');

        $client->request('GET', '/applications?search=alice%40example.com');

        self::assertResponseIsSuccessful();
        $html = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('Alice Smith', $html);
        self::assertStringNotContainsString('Bob Jones', $html);
    }

    public function testListShowsEmptyMessage(): void
    {
        $client = static::createClient();
        $this->cleanDatabase();
        $client->request('GET', '/applications');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('p', 'No applications found.');
    }

    private function seedApplication(string $name, string $email, string $position): void
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $repository = new DoctrineJobApplicationRepository($em);

        $application = JobApplicationMother::builder()
            ->withName($name)
            ->withEmail($email)
            ->withPosition($position)
            ->build();

        $repository->save($application);
    }
}
