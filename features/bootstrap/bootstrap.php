<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = 'test';
(new Dotenv())->bootEnv(__DIR__ . '/../../.env');
