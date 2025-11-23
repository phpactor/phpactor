<?php

namespace Phpactor\CodeTransform\Tests\Adapter\TolerantParser\Refactor;

use Generator;
use Phpactor\CodeTransform\Adapter\TolerantParser\Refactor\TolerantImportName;
use Phpactor\CodeTransform\Domain\Refactor\ImportClass\NameImport;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextEdits;

class TolerantImportNameTest extends AbstractTolerantImportNameCase
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
            'importClass4.test',
            'Barfoo\Foobar',
            'Barfoo',
        ];

        yield 'with static alias' => [
            'importClass5.test',
            'Barfoo\Foobar',
            'Barfoo',
        ];

        yield 'with multiple aliases' => [
            'importClass6.test',
            'Barfoo\Foobar',
            'Barfoo',
        ];

        yield 'with alias and existing name' => [
            'importClass7.test',
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

        yield 'with declare only' => [
            'importClass_with_strict_types.test',
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

    public function testImportsGlobal(): void
    {
        $source = '<?php namespace Foobar;';
        $edits = $this->importName($source, 10, NameImport::forFunction('array_map', null), true);
        self::assertStringContainsString('array_map', $edits->apply($source));
    }

    public function testNotImportGlobalWhenDisabled(): void
    {
        $source = '<?php namespace Foobar;';
        $edits = $this->importName($source, 10, NameImport::forFunction('array_map', null), false);
        self::assertStringNotContainsString('array_map', $edits->apply($source));
    }

    public function testImportNotGlobalWhenDisabled(): void
    {
        $source = '<?php namespace Foobar;';
        $edits = $this->importName($source, 10, NameImport::forFunction('Bar\array_map', null), false);
        self::assertStringContainsString('Bar\array_map', $edits->apply($source));
    }

    protected function importName(string $source, int $offset, NameImport $nameImport, bool $importGlobals = true): TextEdits
    {
        $importClass = (new TolerantImportName($this->updater(), $this->parser(), $importGlobals));
        return $importClass->importName(SourceCode::fromString($source), ByteOffset::fromInt($offset), $nameImport);
    }
}
