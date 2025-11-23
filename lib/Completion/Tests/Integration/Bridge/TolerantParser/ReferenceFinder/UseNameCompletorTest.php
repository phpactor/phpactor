<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\ReferenceFinder;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\Completion\Bridge\TolerantParser\ReferenceFinder\UseNameCompletor;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\DocumentPrioritizer\DefaultResultPrioritizer;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\TolerantCompletorTestCase;
use Phpactor\ReferenceFinder\NameSearcher;
use Phpactor\ReferenceFinder\Search\NameSearchResult;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\ReflectorBuilder;

class UseNameCompletorTest extends TolerantCompletorTestCase
{
    /**
     * @param array{string,array<int,array<string,string>>} $expected
     */
    #[DataProvider('provideComplete')]
    public function testComplete(string $source, array $expected): void
    {
        $this->assertComplete($source, $expected);
    }

    /**
     * @return Generator<string,array{string,array<int,array<string,string>>}>
     */
    public static function provideComplete(): Generator
    {
        yield 'first segment' => [
            '<?php use Fo<>',
 [
                [
                    'type'              => Suggestion::TYPE_CLASS,
                    'name'              => 'Foobar',
                    'short_description' => 'Foobar',
                ]
            ]
        ];
        yield 'second segment' => [
            '<?php use Foobar\Bar<>',
 [
                [
                    'type'              => Suggestion::TYPE_CLASS,
                    'name'              => 'Barfoo',
                    'short_description' => 'Foobar\Barfoo',
                ]
            ]
        ];
    }

    protected function createTolerantCompletor(TextDocument $source): TolerantCompletor
    {
        $searcher = $this->prophesize(NameSearcher::class);
        $searcher->search('\Fo', null)->willYield([
            NameSearchResult::create('class', 'Foobar'),
        ]);

        $searcher->search('\Foobar\Bar', null)->willYield([
            NameSearchResult::create('class', 'Foobar\Barfoo'),
        ]);
        $reflector = ReflectorBuilder::create()->addSource($source)->build();

        return new UseNameCompletor(
            $searcher->reveal(),
            new DefaultResultPrioritizer(),
        );
    }
}
