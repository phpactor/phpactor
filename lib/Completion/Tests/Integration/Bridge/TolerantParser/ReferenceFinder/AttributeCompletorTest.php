<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\ReferenceFinder;

use Generator;
use Phpactor\Completion\Bridge\TolerantParser\ReferenceFinder\AttributeCompletor;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\DocumentPrioritizer\DefaultResultPrioritizer;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\TolerantCompletorTestCase;
use Phpactor\ReferenceFinder\NameSearcher;
use Phpactor\ReferenceFinder\NameSearcherType;
use Phpactor\ReferenceFinder\Search\NameSearchResult;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\ReflectorBuilder;

class AttributeCompletorTest extends TolerantCompletorTestCase
{
    /**
     * @dataProvider provideComplete
     *
     * @param array{string,array<int,array<string,string>>} $expected
     */
    public function testComplete(string $source, array $expected): void
    {
        $this->assertComplete($source, $expected);
    }

    /**
     * @return Generator<string,array{string,array<int,array<string,string>>}>
     */
    public function provideComplete(): Generator
    {
        yield 'new class instance' => [
            '<?php namespace Foo { #[Foo<>]class Bar{}',
 [
                [
                    'type'              => Suggestion::TYPE_CLASS,
                    'name'              => 'Foobar',
                    'short_description' => 'Foobar',
                    'snippet'           => 'Foobar($1)$0',
                ]
            ]
        ];

        yield 'only show children for qualified names' => [
            '<?php namespace Foo { #[Relative\<>]class Bar{}', [
                [
                    'type'              => Suggestion::TYPE_MODULE,
                    'name'              => 'One',
                    'short_description' => 'Foo\Relative\One',
                ],
                [
                    'type'              => Suggestion::TYPE_CLASS,
                    'name'              => 'Two',
                    'short_description' => 'Foo\Relative\Two',
                    'snippet'           => 'Two($1)$0',
                ],
                [
                    'type'              => Suggestion::TYPE_MODULE,
                    'name'              => 'Two',
                    'short_description' => 'Foo\Relative\Two',
                ],
            ],
        ];
    }

    protected function createTolerantCompletor(TextDocument $source): TolerantCompletor
    {
        $searcher = $this->prophesize(NameSearcher::class);
        $searcher->search('Foo', NameSearcherType::ATTRIBUTE)->willYield([
            NameSearchResult::create('class', 'Foobar'),
        ]);
        $searcher->search('\\Foo\\Relative', NameSearcherType::ATTRIBUTE)->willYield([
            NameSearchResult::create('class', 'Foo\Relative\One\Blah\Boo'),
            NameSearchResult::create('class', 'Foo\Relative\One\Glorm\Bar'),
            NameSearchResult::create('class', 'Foo\Relative\One\Blah'),
            NameSearchResult::create('class', 'Foo\Relative\Two'),
            NameSearchResult::create('class', 'Foo\Relative\Two\Glorm\Bar'),
        ]);

        $reflector = ReflectorBuilder::create()->addSource($source)->build();

        return new AttributeCompletor(
            $searcher->reveal(),
            new DefaultResultPrioritizer(),
        );
    }
}
