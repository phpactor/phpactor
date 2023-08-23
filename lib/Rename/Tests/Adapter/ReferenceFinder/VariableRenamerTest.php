<?php

namespace Phpactor\Rename\Tests\Adapter\ReferenceFinder;

use Microsoft\PhpParser\Parser;
use PHPUnit\Framework\TestCase;
use Generator;
use Phpactor\Rename\Adapter\ReferenceFinder\VariableRenamer;
use Phpactor\Rename\Model\LocatedTextEdits;
use Phpactor\Rename\Model\LocatedTextEditsMap;
use Phpactor\Extension\LanguageServerRename\Tests\Util\OffsetExtractor;
use Phpactor\ReferenceFinder\DefinitionAndReferenceFinder;
use Phpactor\ReferenceFinder\PotentialLocation;
use Phpactor\ReferenceFinder\TestDefinitionLocator;
use Phpactor\Rename\Model\ReferenceFinder\PredefinedReferenceFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\LocationRange;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\TextDocument\TextDocumentLocator\InMemoryDocumentLocator;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Core\TypeFactory;

class VariableRenamerTest extends TestCase
{
    const URI = 'file:///test/Class1.php';

    /** @dataProvider provideGetRenameRange */
    public function testGetRenameRange(string $source): void
    {
        $extractor = OffsetExtractor::create()
            ->registerOffset('selection', '<>')
            ->registerRange('expectedRange', '{{', '}}')
            ->parse($source);

        [ $selection ] = $extractor->offsets('selection');
        $expectedRanges = $extractor->ranges('expectedRange');
        $newSource = $extractor->source();

        $expectedRange = count($expectedRanges) > 0 ? $expectedRanges[0] : null;

        $document = TextDocumentBuilder::create($newSource)
            ->uri('file:///test/testDoc')
            ->build();

        $variableRenamer = $this->createRenamer([], null, []);
        $actualRange = $variableRenamer->getRenameRange($document, $selection);
        $this->assertEquals($expectedRange, $actualRange);
    }

    /**
     * @return Generator<string,array{string}>
     */
    public function provideGetRenameRange(): Generator
    {
        yield 'Rename argument' => [
            '<?php class Class1 { public function method1(${{a<>rg1}}){ } }'
        ];

        yield 'Rename variable' => [
            '<?php ${{va<>r1}} = 5;'
        ];

        yield 'Rename dynamic variable' => [
            '<?php class Class1 { public function method1(){ $${{va<>r1}} = 5; } }'
        ];

        yield 'Rename variable in list deconstruction' => [
            '<?php class Class1 { public function method1(){ [ ${{va<>r1}} ] = someFunc(); } }'
        ];

        yield 'Rename variable in anonymous function use statement' => [
            '<?php class Class1 { public function method1(string $var1){ $f = function() use (${{v<>ar1}}) {} } }'
        ];

        yield 'Rename variable in catch statatement' => [
            '<?php class Class1 { public function method1(){ try { } catch(Exception ${{e<>xcp}}) {} } }'
        ];

        yield 'NULL: Rename static property (definition)' => [
            '<?php class Class1 { public static $st<>aticProp; } }'
        ];

        yield 'NULL: Rename property (definition)' => [
            '<?php class Class1 { public $pro<>p; } }'
        ];

        yield 'NULL: Rename property (multiple definition)' => [
            '<?php class Class1 { public $prop1, $pr<>op2; } }'
        ];
    }

    /** @dataProvider provideRename */
    public function testRename(string $source): void
    {
        $extractor = OffsetExtractor::create()
            ->registerOffset('selection', '<>')
            ->registerOffset('definition', '<d>')
            ->registerOffset('references', '<r>')
            ->registerRange('expectedRanges', '{{', '}}')
            ->parse($source);

        $selection = $extractor->offset('selection');
        $definition = $extractor->offset('definition');
        $references = $extractor->offsets('references');
        $expectedRanges = $extractor->ranges('expectedRanges');
        $newName = 'newName';

        $textDocument = TextDocumentBuilder::create($extractor->source())
            ->uri(self::URI)
            ->build();

        $expectedEdits = [];
        foreach ($expectedRanges as $range) {
            assert($range instanceof ByteOffsetRange);
            $expectedEdits[] = TextEdit::create(
                $range->start(),
                $range->end()->toInt() - $range->start()->toInt(),
                $newName
            );
        }

        $renamer = $this->createRenamer(
            array_map(
                function (ByteOffset $reference) use ($textDocument) {
                    return PotentialLocation::surely(
                        new LocationRange(
                            $textDocument->uriOrThrow(),
                            ByteOffsetRange::fromByteOffsets($reference, $reference)
                        )
                    );
                },
                $references
            ),
            new LocationRange($textDocument->uriOrThrow(), ByteOffsetRange::fromByteOffsets($definition, $definition)),
            [ $textDocument ]
        );

        $renamer->getRenameRange($textDocument, $selection);

        $actualResults = iterator_to_array(
            $renamer->rename($textDocument, $selection, $newName),
            false
        );

        $this->assertEquals(
            [
                new LocatedTextEdits(TextEdits::fromTextEdits($expectedEdits), $textDocument->uri())
            ],
            LocatedTextEditsMap::fromLocatedEdits($actualResults)->toLocatedTextEdits()
        );
    }

    /**
     * @return Generator<string,array{string}>
     */
    public function provideRename(): Generator
    {
        yield 'Rename variable' => [
            '<?php class Class1 { function method1(){ <d>${{va<>r1}} = 5; $var2 = <r>${{var1}} + 5; } }'
        ];

        yield 'Rename parameter' => [
            '<?php class Class1 { function method1(<d>${{ar<>g1}}){ $var5 = <r>${{arg1}}; } }'
        ];

        yield 'Rename variable (in list deconstructor)' => [
            '<?php class Class1 { function method1(){ [ <d>${{va<>r1}} ] = 5; $var2 = <r>${{var1}} + 5; } }'
        ];

        yield 'Rename variable (in list deconstructor with key)' => [
            '<?php class Class1 { function method1(){ [ "key"=><d>${{va<>r1}} ] = 5; $var2 = <r>${{var1}} + 5; } }'
        ];

        yield 'Rename variable (in list function no key)' => [
            '<?php class Class1 { function method1(){ list(<d>${{va<>r1}}) = 5; $var2 = <r>${{var1}} + 5; } }'
        ];

        yield 'Rename variable (in list function with key)' => [
            '<?php class Class1 { function method1(){ list("key"=><d>${{va<>r1}}) = 5; $var2 = <r>${{var1}} + 5; } }'
        ];

        yield 'Rename variable (as foreach array)' => [
            '<?php class Class1 { function method1(){ <d>${{var}} = []; foreach(<r>${{v<>ar}} as $val) { } } }'
        ];

        yield 'Rename variable (as foreach value)' => [
            '<?php class Class1 { function method1(){ $var = []; foreach($var as <d>${{val}}) { <r>${{v<>al}} += 5; } } }'
        ];

        yield 'Rename variable (as foreach key)' => [
            '<?php class Class1 { function method1(){ $var = []; foreach($var as <d>${{key}}=>$val) { <r>${{k<>ey}} += 5; } } }'
        ];

        yield 'Rename argument' => [
            '<?php class Class1 { function method1(Class2 <d>${{ar<>g1}}){ <r>${{arg1}} = 5; $var2 = <r>${{arg1}} + 5; } }'
        ];

        yield 'Rename argument (no hint)' => [
            '<?php class Class1 { function method1(<d>${{ar<>g1}}){ <r>${{arg1}} = 5; $var2 = <r>${{arg1}} + 5; } }'
        ];

        yield 'Rename foreach variable' => [
            '<?php $var1 = 0; foreach($array as <d>${{value}}) { echo <r>${{val<>ue}}; }'
        ];
    }

    /**
     * @param PotentialLocation[] $references
     * @param TextDocument[] $textDocuments
     */
    private function createRenamer(array $references, ?LocationRange $defintionLocation, array $textDocuments): VariableRenamer
    {
        $variableRenamer = new VariableRenamer(
            new DefinitionAndReferenceFinder(
                TestDefinitionLocator::fromSingleLocation(TypeFactory::unknown(), $defintionLocation),
                new PredefinedReferenceFinder(...$references),
            ),
            InMemoryDocumentLocator::fromTextDocuments($textDocuments),
            new Parser()
        );
        return $variableRenamer;
    }
}
