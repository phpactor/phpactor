<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\WorseReflection;

use Generator;
use Microsoft\PhpParser\Parser;
use PHPUnit\Framework\TestCase;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\Completor\DocblockCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Name\FullyQualifiedName;
use Phpactor\ReferenceFinder\Search\NameSearchResult;
use Phpactor\ReferenceFinder\Search\PredefinedNameSearcher;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;

class DocblockCompletorTest extends TestCase
{
    /**
     * @dataProvider provideComplete
     * @param array<string> $expected
     */
    public function testComplete(string $source, array $expected): void
    {
        $results = [
            NameSearchResult::create(
                'class',
                FullyQualifiedName::fromString('Namespace\Aardvark')
            ),
        ];

        [$source, $offset] = ExtractOffset::fromSource($source);
        $node = (new Parser())->parseSourceFile($source)->getDescendantNodeAtPosition((int)$offset);
        $suggestions = iterator_to_array((new DocblockCompletor(new PredefinedNameSearcher($results)))->complete(
            $node,
            TextDocumentBuilder::create($source)->build(),
            ByteOffset::fromInt((int)$offset)
        ));
        self::assertEquals($expected, array_map(fn (Suggestion $s) => $s->name(), $suggestions));
    }

    /**
     * @return Generator<mixed>
     */
    public function provideComplete(): Generator
    {
        yield 'not in docblock' => [
            '@param<>',
            []
        ];

        yield 'in docblock' => [
            '/** @para<> */',
            [
                '@param',
            ]
        ];

        yield 'in second-line docblock' => [
            '* @para<> */',
            [
                '@param',
            ]
        ];

        yield 'in second-line docblock with more spaces' => [
            '   *    @para<> */',
            [
                '@param',
            ]
        ];

        yield 'bare ampersand' => [
            '   *    @<>',
            DocblockCompletor::SUPPORTED_TAGS,
        ];

        yield 'param type' => [
            '   *    @param A<> */',
            [
                'Aardvark',
            ],
        ];

        yield 'param type no match' => [
            '   *    @param Zed<> */',
            [
            ],
        ];

        yield 'var type match' => [
            '   *    @var Aar<> */',
            [
                'Aardvark',
            ],
        ];
    }
}
