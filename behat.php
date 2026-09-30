<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Extension;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withExtension(new Extension(
                'FriendsOfBehat\SymfonyExtension\ServiceContainer\SymfonyExtension',
                [
                    'bootstrap' => 'features/bootstrap/bootstrap.php',
                    'kernel' => [
                        'class' => 'App\Kernel',
                        'environment' => 'test',
                        'debug' => true,
                    ],
                ]
            ))
            ->withSuite(
                (new Suite('default'))
                    ->withContexts('App\Tests\Behat\FeatureContext')
                    ->withPaths('features')
            )
    );
