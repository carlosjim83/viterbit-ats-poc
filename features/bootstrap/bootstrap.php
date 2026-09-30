<?php

declare(strict_types=1);

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__ . '/../../vendor/autoload.php';

$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = 'test';
(new Dotenv())->bootEnv(__DIR__ . '/../../.env');

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

// Disable DAMADoctrineTestBundle static connections for Behat E2E tests
// so Symfony Messenger can commit transactions normally.
DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver::setKeepStaticConnections(false);

// Ensure test database exists and migrations are run before Behat scenarios
$kernel = new App\Kernel('test', true);
$kernel->boot();

$app = new Application($kernel);
$app->setAutoExit(false);

$app->run(new ArrayInput([
    'command' => 'doctrine:database:create',
    '--if-not-exists' => true,
]), new NullOutput());

$app->run(new ArrayInput([
    'command' => 'doctrine:migrations:migrate',
    '--no-interaction' => true,
]), new NullOutput());
