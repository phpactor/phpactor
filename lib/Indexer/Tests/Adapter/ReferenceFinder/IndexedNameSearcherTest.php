<?php

namespace Phpactor\Indexer\Tests\Adapter\ReferenceFinder;

use Generator;
use Phpactor\Indexer\Adapter\ReferenceFinder\IndexedNameSearcher;
use Phpactor\Indexer\Tests\Adapter\IndexTestCase;
use Phpactor\ReferenceFinder\NameSearcherType;
use Phpactor\ReferenceFinder\Search\NameSearchResult;

class IndexedNameSearcherTest extends IndexTestCase
{
    private const ATTR_WORKSPACE = [
        'project/Baj.php' => '<?php #[Attribute(\Attribute::TARGET_CLASS)] class Baj {}',
        'project/Bacc.php' => '<?php #[Attribute(\Attribute::TARGET_CLASS_CONSTANT)] class Bacc {}',
        'project/Baf.php' => '<?php #[Attribute(\Attribute::TARGET_FUNCTION)] class Baf {}',
        'project/Bam.php' => '<?php #[Attribute] class Bam {}',
        'project/Bap.php' => '<?php use Attribute as PHPAttribute; #[PHPAttribute(\Attribute::TARGET_PARAMETER)] class Bap {}',
        'project/Bar.php' => '<?php #[\Attribute(\Attribute::TARGET_PROPERTY)] class Bar {}',
        'project/Foo/Bax.php' => '<?php namespace Foo; use Not\Attribute; #[Attribute] class Bax {}',
        'project/Baz.php' => '<?php class Baz {}',
        'project/Attribute/Bak.php' => '<?php namespace Attribute; #[\Attribute(\Attribute::TARGET_METHOD)] readonly class Bak {}',
    ];

    public function testSearcherWithAbsolute(): void
    {
        $this->workspace()->put('project/Foobar.php', '<?php class Foobar {}');
        $this->workspace()->put('project/Barfoo.php', '<?php namespace Bar; class Foobar {}');
        $this->workspace()->put('project/Barfoo.php', '<?php namespace Foo; class Foobar {}');
        $agent = $this->indexAgent();
        $agent->indexer()->getJob()->run();
        $searcher = new IndexedNameSearcher($agent->search());

        $results = iterator_to_array($searcher->search('\Foo'));

        self::assertCount(2, $results, 'Returns both root class name and namespace match');
    }

    public function testSearcher(): void
    {
        $this->workspace()->put('project/Foobar.php', '<?php class Foobar {}');
        $agent = $this->indexAgent();
        $agent->indexer()->getJob()->run();
        $searcher = new IndexedNameSearcher($agent->search());

        foreach ($searcher->search('Foo') as $result) {
            assert($result instanceof NameSearchResult);
            self::assertEquals('Foobar', $result->name()->head()->__toString());
            self::assertNotNull($result->uri());
            self::assertStringContainsString('Foobar.php', $result->uri()->__toString());
        }
    }

    public function testSearcherForInterface(): void
    {
        $this->workspace()->put('project/Foobar.php', '<?php interface Foobar {}');
        $agent = $this->indexAgent();
        $agent->indexer()->getJob()->run();
        $searcher = new IndexedNameSearcher($agent->search());

        foreach ($searcher->search('Foo', NameSearcherType::INTERFACE) as $result) {
            assert($result instanceof NameSearchResult);
            self::assertEquals('Foobar', $result->name()->head()->__toString());
            self::assertNotNull($result->uri());
            self::assertStringContainsString('Foobar.php', $result->uri()->__toString());
            return;
        }

        $this->fail('Could not find interace');
    }

    public function testSearcherForEnum(): void
    {
        $this->workspace()->put('project/Foobar.php', '<?php enum Foobar {}');
        $agent = $this->indexAgent();
        $agent->indexer()->getJob()->run();
        $searcher = new IndexedNameSearcher($agent->search());

        foreach ($searcher->search('Foo', NameSearcherType::ENUM) as $result) {
            assert($result instanceof NameSearchResult);
            self::assertEquals('Foobar', $result->name()->head()->__toString());
            self::assertNotNull($result->uri());
            self::assertStringContainsString('Foobar.php', $result->uri()->__toString());
            return;
        }

        $this->fail('Could not find enum');
    }

    public function testSearcherForTrait(): void
    {
        $this->workspace()->put('project/Foobar.php', '<?php trait Foobar {}');
        $agent = $this->indexAgent();
        $agent->indexer()->getJob()->run();
        $searcher = new IndexedNameSearcher($agent->search());

        foreach ($searcher->search('Foo', NameSearcherType::TRAIT) as $result) {
            assert($result instanceof NameSearchResult);
            self::assertEquals('Foobar', $result->name()->head()->__toString());
            self::assertNotNull($result->uri());
            self::assertStringContainsString('Foobar.php', $result->uri()->__toString());
            return;
        }

        $this->fail('Could not find trait');
    }

    /**
     * @dataProvider provideWorkspaceToSearchAttributes
     * @param NameSearcherType::* $type
     * @param string[] $expectedResultPaths
     */
    public function testSearcherForAttribute(string $query, string $type, array $expectedResultPaths): void
    {
        foreach (self::ATTR_WORKSPACE as $path => $contents) {
            $this->workspace()->put($path, $contents);
        }

        $agent = $this->indexAgent();
        $agent->indexer()->getJob()->run();
        $searcher = new IndexedNameSearcher($agent->search());

        $resultPaths = [];
        $offset = 1 + mb_strlen($this->workspace()->path());
        foreach ($searcher->search($query, $type) as $result) {
            assert($result instanceof NameSearchResult);
            self::assertNotNull($result->uri());
            $resultPaths[] = mb_substr($result->uri()->path(), $offset);
        }

        self::assertEqualsCanonicalizing($expectedResultPaths, $resultPaths);
    }

    /**
     * @return Generator<string,array{query:string,type:string,expectedResultPaths:array<int,string>}>
     */
    public function provideWorkspaceToSearchAttributes(): Generator
    {
        yield 'not targeted attributes' => [
            'query' => 'Ba',
            'type' => NameSearcherType::ATTRIBUTE,
            'expectedResultPaths' => [
                'project/Baf.php',
                'project/Bacc.php',
                'project/Bap.php',
                'project/Bar.php',
                'project/Baj.php',
                'project/Bam.php',
                'project/Attribute/Bak.php',
            ],
        ];

        yield 'class attributes' => [
            'query' => 'Ba',
            'type' => NameSearcherType::ATTRIBUTE_TARGET_CLASS,
            'expectedResultPaths' => [
                'project/Baj.php',
                'project/Bam.php',
            ],
        ];

        yield 'property attributes' => [
            'query' => 'Ba',
            'type' => NameSearcherType::ATTRIBUTE_TARGET_PROPERTY,
            'expectedResultPaths' => [
                'project/Bar.php',
                'project/Bam.php',
            ],
        ];

        yield 'promoted property attributes' => [
            'query' => 'Ba',
            'type' => NameSearcherType::ATTRIBUTE_TARGET_PROMOTED_PROPERTY,
            'expectedResultPaths' => [
                'project/Bar.php',
                'project/Bap.php',
                'project/Bam.php',
            ],
        ];

        yield 'method attributes' => [
            'query' => 'Ba',
            'type' => NameSearcherType::ATTRIBUTE_TARGET_METHOD,
            'expectedResultPaths' => [
                'project/Attribute/Bak.php',
                'project/Bam.php',
            ],
        ];

        yield 'parameter attributes' => [
            'query' => 'Ba',
            'type' => NameSearcherType::ATTRIBUTE_TARGET_PARAMETER,
            'expectedResultPaths' => [
                'project/Bap.php',
                'project/Bam.php',
            ],
        ];

        yield 'class constant attributes' => [
            'query' => 'Ba',
            'type' => NameSearcherType::ATTRIBUTE_TARGET_CLASS_CONSTANT,
            'expectedResultPaths' => [
                'project/Bacc.php',
                'project/Bam.php',
            ],
        ];

        yield 'function attributes' => [
            'query' => 'Ba',
            'type' => NameSearcherType::ATTRIBUTE_TARGET_FUNCTION,
            'expectedResultPaths' => [
                'project/Baf.php',
                'project/Bam.php',
            ],
        ];
    }
}
