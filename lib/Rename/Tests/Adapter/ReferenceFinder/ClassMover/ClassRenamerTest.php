<?php

namespace Phpactor\Rename\Tests\Adapter\ReferenceFinder\ClassMover;

use Generator;
use Microsoft\PhpParser\Parser;
use Phpactor\ClassMover\ClassMover;
use Phpactor\Rename\Adapter\ReferenceFinder\ClassMover\ClassRenamer;
use Phpactor\Rename\Model\LocatedTextEdits;
use Phpactor\Rename\Model\LocatedTextEditsMap;
use Phpactor\Rename\Model\NameToUriConverter;
use Phpactor\Rename\Tests\Adapter\ReferenceFinder\ReferenceRenamerIntegrationTestCase;
use Phpactor\Extension\LanguageServerRename\Tests\Util\OffsetExtractor;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\TextDocument\TextDocumentLocator\InMemoryDocumentLocator;
use Phpactor\TextDocument\TextDocumentUri;

class ClassRenamerTest extends ReferenceRenamerIntegrationTestCase
{
    /**
     * @dataProvider provideRename
     */
    public function testRename(
        string $oldPath,
        string $source,
        string $newName,
        ?string $newUri,
        int $expectedEditsCount,
        ?string $expected
    ): void {
        $extractor = OffsetExtractor::create()
            ->registerOffset('offset', '<>')
            ->registerOffset('r', '<r>')
            ->parse($source);

        $offset = $extractor->offset('offset');
        $references = $extractor->offsets('r');

        $source = $extractor->source();

        $textDocument = TextDocumentBuilder::create($source)->uri($oldPath)->build();

        $renamer = $this->createRenamer('/foo/', $references, $textDocument);
        self::assertNotNull($renamer->getRenameRange($textDocument, $offset));
        $rename = $renamer->rename($textDocument, $offset, $newName);

        $actualResults = iterator_to_array($rename, false);

        $renameResult = $rename->getReturn();
        self::assertSame($newUri, $renameResult ? $renameResult->newUri()->__toString() : null);

        $edits = LocatedTextEditsMap::fromLocatedEdits($actualResults);
        $locateds = $edits->toLocatedTextEdits();
        self::assertCount($expectedEditsCount, $locateds);

        if (0 === $expectedEditsCount) {
            return;
        }
        $located = reset($locateds);
        assert($located instanceof LocatedTextEdits);
        self::assertEquals($expected, $located->textEdits()->apply($source));
    }

    /**
     * @return Generator<string,array{string,string,string,null|string,int,null|string}>
     */
    public function provideRename(): Generator
    {
        yield 'class' => [
            '/foo/Class1.php',
            '<?php <r>class Cl<>ass1 { }',
            'Class2',
            'file:///foo/Class2.php',
            1,
            '<?php class Class2 { }',
        ];

        yield 'class: no change' => [
            '/foo/Class1.php',
            '<?php <r>class Cl<>ass1 { }',
            'Class1',
            null,
            0,
            null,
        ];

        yield 'interface' => [
            '/foo/Interface1.php',
            '<?php <r>interface Inter<>face1 { }',
            'Interface2',
            'file:///foo/Interface2.php',
            1,
            '<?php interface Interface2 { }',
        ];

        yield 'enum' => [
            '/foo/Enum1.php',
            '<?php <r>enum Mar<>ker { }',
            'Pony',
            'file:///foo/Pony.php',
            1,
            '<?php enum Pony { }',
        ];

        yield 'interface: updates implements' => [
            '/foo/Interface1.php',
            '<?php <r>interface Inter<>face1 { } class Class1 implements Interface1 { }',
            'Interface2',
            'file:///foo/Interface2.php',
            1,
            '<?php interface Interface2 { } class Class1 implements Interface2 { }',
        ];

        yield 'trait' => [
            '/foo/Trait1.php',
            '<?php <r>trait Tra<>it1 { }',
            'Trait2',
            'file:///foo/Trait2.php',
            1,
            '<?php trait Trait2 { }',
        ];

        yield 'trait: updates uses' => [
            '/foo/Trait1.php',
            '<?php <r>trait Tra<>it1 { } class Class1 { use Trait1; }',
            'Trait2',
            'file:///foo/Trait2.php',
            1,
            '<?php trait Trait2 { } class Class1 { use Trait2; }',
        ];

        yield 'namespaced class' => [
            '/foo/Foo/Class1.php',
            '<?php namespace Foo; <r>class Cl<>ass1 { }',
            'Class2',
            'file:///foo/Foo/Class2.php',
            1,
            '<?php namespace Foo; class Class2 { }',
        ];

        yield 'class reference' => [
            '/foo/Foo/Class1.php',
            '<?php namespace Foo; <r>class Class1 { } <r>Cla<>ss1::foo();',
            'Class2',
            'file:///foo/Foo/Class2.php',
            1,
            '<?php namespace Foo; class Class2 { } Class2::foo();',
        ];

        yield 'updates namespaced imported class' => [
            '/foo/Foo/Class1.php',
            '<?php use Foo\Class1; <r>Cla<>ss1::foo();',
            'Class2',
            'file:///foo/Foo/Class2.php',
            1,
            '<?php use Foo\Class2; Class2::foo();',
        ];

        yield 'updates imported class' => [
            '/foo/Class1.php',
            '<?php use Class1; <r>Cla<>ss1::foo();',
            'Class2',
            'file:///foo/Class2.php',
            1,
            '<?php use Class2; Class2::foo();',
        ];
    }

    /**
     * @param ByteOffset[] $references
     */
    private function createRenamer(
        string $namespaceRootDir,
        array $references,
        TextDocument $textDocument
    ): ClassRenamer {
        $nameToUriConverter = new class($namespaceRootDir) implements NameToUriConverter {
            public function __construct(private string $namespaceRootDir)
            {
            }

            public function convert(string $className): TextDocumentUri
            {
                return TextDocumentUri::fromString(sprintf(
                    '%s%s.php',
                    $this->namespaceRootDir,
                    str_replace('\\', '/', $className),
                ));
            }
        };

        return new ClassRenamer(
            $nameToUriConverter,
            $nameToUriConverter,
            $this->offsetsToReferenceFinder($textDocument, $references),
            InMemoryDocumentLocator::fromTextDocuments([$textDocument]),
            new Parser(),
            new ClassMover()
        );
    }
}
