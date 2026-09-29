<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->exclude('var')
    ->exclude('vendor');

return (new Config())
    ->setRules([
        '@PSR12' => true,
        '@Symfony' => true,
        'declare_strict_types' => true,
        'strict_param' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => true,
        'no_unused_imports' => true,
        'return_type_declaration' => true,
        'phpdoc_separation' => true,
        'phpdoc_align' => true,
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true);
