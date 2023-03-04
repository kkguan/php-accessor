<?php

/*
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!file_exists(__DIR__ . '/src')) {
    exit(0);
}

$fileHeaderComment = <<<'EOF'
This file is part of the PhpAccessor package.
For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PHP71Migration' => true,
        '@PHPUnit75Migration:risky' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'protected_to_private' => false,
        'concat_space' => [
            'spacing' => 'one',
        ],
        'native_constant_invocation' => ['strict' => false],
        'nullable_type_declaration_for_default_null_value' => ['use_nullable_type_declaration' => false],
        'no_superfluous_phpdoc_tags' => ['remove_inheritdoc' => true],
        'header_comment' => ['header' => $fileHeaderComment],
        'modernize_strpos' => true,
        'get_class_to_class_keyword' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
        ],
    ])
    ->setRiskyAllowed(true)
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude('vendor')
            ->in(__DIR__)
    )
    ->setUsingCache(false);
