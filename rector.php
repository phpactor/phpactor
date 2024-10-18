<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php84\Rector\Param\ExplicitNullableParamTypeRector;

return RectorConfig::configure()
    ->withImportNames()
    ->withPaths([
        __DIR__ . '/lib',
        __DIR__ . '/tests',
    ])
    ->withSkipPath('*/Workspace/*')
    ->withSkipPath('/tests/Assets/*')
    ->withSkipPath('/*/examples/*')
    ->withRules([
        ExplicitNullableParamTypeRector::class,
    ]);
