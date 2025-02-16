<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->append([
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->exclude(['bin', 'var', 'vendor', 'web', 'ci', 'node_modules'])
    ->notPath('rector.php')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'multiline_comment_opening_closing' => true,
        'fully_qualified_strict_types' => true,
        'binary_operator_spaces' => ['operators' => ['=>' => 'align', '=' => 'align']],
    ])
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setFinder($finder)
;
