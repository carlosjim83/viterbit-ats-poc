<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\Application\Handler;

use App\Application\Application\Command\SubmitApplication;
use App\Application\Domain\Model\ValueObject\ApplicationId;
use Doctrine\ORM\EntityManagerInterface;
use Ecotone\Modelling\CommandBus;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class SubmitApplicationHandlerTest extends KernelTestCase
{
    private CommandBus $commandBus;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();

        /** @var CommandBus $commandBus */
        $commandBus = self::getContainer()->get(CommandBus::class);
        $this->commandBus = $commandBus;

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $this->em = $em;

        $conn = $this->em->getConnection();
        $conn->executeStatement('DELETE FROM job_applications');
    }

    public function testDispatchesCommandAndPersistsApplication(): void
    {
        $command = new SubmitApplication(
            'Jane Doe',
            'jane@example.com',
            'Product Manager',
            '10 years of experience',
        );

        /** @var ApplicationId $id */
        $id = $this->commandBus->sendWithRouting('application.submit', $command);

        $idString = (string) $id;
        self::assertNotEmpty($idString);
        self::assertTrue(Uuid::isValid($idString));

        $conn = $this->em->getConnection();
        $row = $conn->fetchAssociative('SELECT * FROM job_applications WHERE id = ?', [$idString]);

        self::assertNotFalse($row);
        self::assertSame('Jane Doe', $row['full_name']);
        self::assertSame('jane@example.com', $row['email']);
        self::assertSame('Product Manager', $row['position']);
        self::assertSame('10 years of experience', $row['cv_text']);
        self::assertSame('received', $row['status']);
    }
}
