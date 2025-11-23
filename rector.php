<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\PHPUnit100\Rector\Class_\AddProphecyTraitRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
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
    ->withSets([
        PHPUnitSetList::PHPUNIT_100,
    ])
    ->withRules([
        ExplicitNullableParamTypeRector::class,
    ]);
