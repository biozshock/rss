<?php

$finder = PhpCsFixer\Finder::create()
    ->name('*.php')
    ->in(__DIR__)
    ->exclude('Library')
    ->exclude('vendor');

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'phpdoc_to_comment' => false,
        'ordered_imports' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_unused_imports' => true,
        'no_alternative_syntax' => true,
        'concat_space' => ['spacing' => 'one'],
    ])
    ->setFinder($finder);
