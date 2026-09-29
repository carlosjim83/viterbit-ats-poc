<?php

declare(strict_types=1);

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__).'/.env', 'test');

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

// Create test database and run migrations before the test suite starts
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
