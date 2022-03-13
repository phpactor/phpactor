<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in('lib')
    ->in('tests')
    ->exclude([
        'Workspace',
        'Assets/Cache',
        'Assets/Projects',
        'Assets/Workspace',
    ])
;

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2' => true,
        'no_unused_imports' => true,
        'phpdoc_to_property_type' => true,
        'no_superfluous_phpdoc_tags' => [
            'remove_inheritdoc' => true,
            'allow_mixed' => true,
        ],
        'class_attributes_separation' => [
            'elements' => [
                'const' => 'only_if_meta',
                'property' => 'one',
                'trait_import' => 'only_if_meta',
            ],
        ],
        'ordered_class_elements' => true,
        'no_empty_phpdoc' => true,
        'phpdoc_trim' => true,
        'array_syntax' => ['syntax' => 'short'],
        'void_return' => true,
        'ordered_class_elements' => true,
        'single_quote' => true,
        'heredoc_indentation' => true,
        'global_namespace_import' => true,
    ])
    ->setFinder($finder)
;

