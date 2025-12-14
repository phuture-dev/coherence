<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->name('*.php');

return (new Config())
    ->setRules([
        'ordered_imports' => [
            'sort_algorithm' => 'length',
            'imports_order' => [
                'class',
                'function',
                'const'
            ],
        ],
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => false,
            'import_functions' => false,
        ],
        'no_unused_imports' => true,
        'blank_line_after_opening_tag' => true,
        'blank_line_after_namespace' => true,
    ])
    ->setFinder($finder);
