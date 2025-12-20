<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->name('*.php');

return (new Config())
    ->setRules([
        '@PSR12' => true,
        'group_import' => true,
        'single_import_per_statement' => false,
        'ordered_imports' => [
            'sort_algorithm' => 'length',
            'imports_order' => [
                'class',
                'function',
                'const'
            ],
        ],
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'constant',
                'property',
                'construct',
                'magic',
                'method_public',
                'method_protected',
                'method_private'
            ],
            'sort_algorithm' => 'alpha'
        ],
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => false,
            'import_functions' => false
        ],
        'no_unused_imports' => true,
        'blank_line_after_opening_tag' => true,
        'blank_line_after_namespace' => true,
        'blank_line_before_statement' => ['statements' => ['return']],
        'array_syntax' => ['syntax' => 'short']
    ])
    ->setFinder($finder);
