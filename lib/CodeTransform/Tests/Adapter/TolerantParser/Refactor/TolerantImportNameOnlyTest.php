<?php

namespace Phpactor\CodeTransform\Tests\Adapter\TolerantParser\Refactor;

use Generator;
use Phpactor\CodeTransform\Adapter\TolerantParser\Refactor\TolerantImportName;
use Phpactor\CodeTransform\Domain\Refactor\ImportClass\NameImport;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextEdits;

class TolerantImportNameOnlyTest extends AbstractTolerantImportNameCase
{
    public static function provideImportClass(): Generator
    {
        yield 'with existing class imports' => [
            'importClass1.test',
            'Barfoo\Foobar',
        ];

        yield 'with namespace' => [
            'importClass2.test',
            'Barfoo\Foobar',
        ];

        yield 'with no namespace declaration or use statements' => [
            'importClass3.test',
            'Barfoo\Foobar',
        ];

        yield 'with alias' => [
            'importOnlyClass4.test',
            'Barfoo\Foobar',
            'Barfoo',
        ];

        yield 'with static alias' => [
            'importOnlyClass5.test',
            'Barfoo\Foobar',
            'Barfoo',
        ];

        yield 'with multiple aliases' => [
            'importOnlyClass6.test',
            'Barfoo\Foobar',
            'Barfoo',
        ];

        yield 'with alias and existing name' => [
            'importOnlyClass7.test',
            'Barfoo\Foobar',
            'Barfoo',
        ];

        yield 'with class in root namespace' => [
            'importClass8.test',
            'Foobar',
        ];

        yield 'from phpdoc' => [
            'importClass9.test',
            'Barfoo\Foobar',
        ];

        yield 'from phpdoc (resolved in a SourceFileNode)' => [
            'importClass10.test',
            'Barfoo\Foobar',
        ];
    }

    public static function provideImportFunction(): Generator
    {
        yield 'import function' => [
            'importFunction1.test',
            'Acme\foobar',
        ];
    }

    protected function importName($source, int $offset, NameImport $nameImport, bool $importGlobals = true): TextEdits
    {
        $importClass = (new TolerantImportName($this->updater(), $this->parser(), $importGlobals));
        return $importClass->importNameOnly(SourceCode::fromString($source), ByteOffset::fromInt($offset), $nameImport);
    }
}
