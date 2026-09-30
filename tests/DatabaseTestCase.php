<?php

declare(strict_types=1);

namespace App\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class DatabaseTestCase extends KernelTestCase
{
    use DatabaseTestTrait;

    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $this->em = $em;

        // Ensure a clean state for each test, as DAMADoctrineTestBundle may leave
        // data behind when the connection was established before PHPUnitExtension
        // had a chance to enable StaticDriver.
        $em->getConnection()->executeStatement('DELETE FROM job_applications');
    }

    protected function tearDown(): void
    {
        // Intentionally not calling parent::tearDown() which shuts down the kernel.
        // DAMA rolls back the transaction between tests; keeping the kernel alive
        // preserves the static database connection.
    }
}
