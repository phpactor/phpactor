<?php

namespace Phpactor\Rename\Tests\Adapter\ReferenceFinder;

use Microsoft\PhpParser\Parser;
use PHPUnit\Framework\TestCase;
use Generator;
use Phpactor\Extension\LanguageServerRename\Tests\Unit\PredefiniedImplementationFinder;
use Phpactor\Extension\LanguageServerRename\Tests\Util\OffsetExtractor;
use Phpactor\Rename\Adapter\ReferenceFinder\MemberRenamer;
use Phpactor\Rename\Model\LocatedTextEdits;
use Phpactor\Rename\Model\LocatedTextEditsMap;
use Phpactor\ReferenceFinder\PotentialLocation;
use Phpactor\Rename\Model\ReferenceFinder\PredefinedReferenceFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\Locations;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\TextDocument\TextDocumentLocator\InMemoryDocumentLocator;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;

class MemberRenamerTest extends TestCase
{
    const EXAMPLE_DOCUMENT_URI = 'file:///test/Class1.php';

    /**
     * @dataProvider provideGetRenameRange
     */
    public function testGetRenameRange(string $source): void
    {
        $extractor = OffsetExtractor::create()
            ->registerOffset('selection', '<>')
            ->registerRange('expectedRange', '{{', '}}')
            ->parse($source);

        $selection = $extractor->offset('selection');
        $expectedRanges = $extractor->ranges('expectedRange');
        $newSource = $extractor->source();

        $expectedRange = count($expectedRanges) > 0 ? $expectedRanges[0] : null;

        $document = TextDocumentBuilder::create($newSource)
            ->uri('file:///test/testDoc')
            ->build();

        $variableRenamer = new MemberRenamer(
            new PredefinedReferenceFinder(...[]),
            InMemoryDocumentLocator::fromTextDocuments([]),
            new Parser(),
            new PredefiniedImplementationFinder(new Locations([])),
        );

        $actualRange = $variableRenamer->getRenameRange($document, $selection);

        $this->assertEquals($expectedRange, $actualRange);
    }

    /**
     * @return Generator<string,array{string}>
     */
    public function provideGetRenameRange(): Generator
    {
        yield 'method declaration' => [
            '<?php class Class1 { public function {{me<>thod1}}(){ } }'
        ];
        yield 'method call' => [
            '<?php $foo->{{me<>thod1}}(); }'
        ];
        yield 'static method call' => [
            '<?php Foobar::{{me<>thod1}}(); }'
        ];
        yield 'property declaration' => [
            '<?php class Class1 { public ${{prop<>erty}}; }'
        ];
        yield 'property access' => [
            '<?php $foo->${{me<>thod1}};'
        ];
        yield 'static property access' => [
            '<?php Foobar::${{me<>thod1}}; }'
        ];
        yield 'constant declaration' => [
            '<?php class Class1 { const {{F<>OO}}="bar"; }'
        ];
        yield 'constant access' => [
            '<?php Foo::{{F<>OO}};'
        ];
    }

    /**
     * @dataProvider provideRename
     */
    public function testRename(string $source): void
    {
        $extractor = OffsetExtractor::create()
            ->registerOffset('selection', '<>')
            ->registerOffset('references', '<r>')
            ->registerOffset('implementations', '<i>')
            ->registerRange('resultEditRanges', '{{', '}}')
            ->parse($source);

        $selection = $extractor->offset('selection');
        $references = $extractor->offsets('references');
        $implementations = $extractor->offsets('implementations');
        $resultEditRanges = $extractor->ranges('resultEditRanges');
        $newSource = $extractor->source();

        $newName = 'newName';

        $textDocument = TextDocumentBuilder::create($newSource)
            ->uri(self::EXAMPLE_DOCUMENT_URI)
            ->build();

        $renamer = new MemberRenamer(
            new PredefinedReferenceFinder(...array_map(function (ByteOffset $reference) use ($textDocument) {
                return PotentialLocation::surely(
                    new Location($textDocument->uri(), ByteOffsetRange::fromByteOffset($reference))
                );
            }, $references)),
            InMemoryDocumentLocator::fromTextDocuments([$textDocument]),
            new Parser(),
            new PredefiniedImplementationFinder(new Locations(array_map(function (ByteOffset $reference) use ($textDocument) {
                return new Location($textDocument->uri(), ByteOffsetRange::fromByteOffset($reference));
            }, $implementations))),
        );

        $resultEdits = [];
        foreach ($resultEditRanges as $range) {
            assert($range instanceof ByteOffsetRange);
            $resultEdits[] = TextEdit::create(
                $range->start(),
                $range->end()->toInt() - $range->start()->toInt(),
                $newName
            );
        }

        $renamer->getRenameRange($textDocument, $selection);
        $actualResults = iterator_to_array($renamer->rename($textDocument, $selection, $newName), false);
        $this->assertEquals(
            [
                new LocatedTextEdits(
                    TextEdits::fromTextEdits($resultEdits),
                    $textDocument->uri()
                )
            ],
            LocatedTextEditsMap::fromLocatedEdits($actualResults)->toLocatedTextEdits()
        );
    }

    /**
     * @return Generator<string,array{string}>
     */
    public function provideRename(): Generator
    {
        yield 'method declaration' => [
            '<?php class Class1 { function {{<r>meth<>od1}}() { } }'
        ];
        yield 'method call' => [
            '<?php $foo->{{<r>me<>thod1}}(); }'
        ];
        yield 'method calls' => [
            '<?php $foo->{{<r>me<>thod1}}(); $foo->{{<r>me<>thod1}}();}'
        ];
        yield 'static method call' => [
            '<?php Foobar::{{<r>me<>thod1}}(); }'
        ];
        yield 'property and definition' => [
            '<?php class Foobar { <r>private ${{foobar}}; function bar() { return $this->{{<r>fo<>obar}}; } }'
        ];
        yield 'constant and definition' => [
            '<?php class Foobar { <r>const {{FOO}}="bar"; function bar() { return self::{{<r>F<>OO}}; } }'
        ];
        yield 'definition and constant' => [
            '<?php class Foobar { <r>const {{F<>OO}}="bar"; function bar() { return self::{{<r>FOO}}; } }'
        ];
    }
}
