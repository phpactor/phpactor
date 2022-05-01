<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\WorseReflection\Completor;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\Completion\Bridge\WorseReflection\Completor\DocblockCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;

class DocblockCompletorTest extends TestCase
{
    /**
     * @dataProvider provideComplete
     */
    public function testComplete(string $source, array $expected): void
    {
        [$source, $offset] = ExtractOffset::fromSource($source);
        $suggestions = iterator_to_array((new DocblockCompletor())->complete(
            TextDocumentBuilder::create($source)->build(),
            ByteOffset::fromInt($offset)
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
            '   *    @<> */',
            DocblockCompletor::SUPPORTED_TAGS,
        ];
    }
}
