<?php

namespace Phpactor\Indexer\Tests\Adapter\ReferenceFinder;

use Phpactor\Indexer\Adapter\ReferenceFinder\IndexedNameSearcher;
use Phpactor\Indexer\Tests\Adapter\IndexTestCase;
use Phpactor\ReferenceFinder\NameSearcherType;
use Phpactor\ReferenceFinder\Search\NameSearchResult;

class IndexedNameSearcherTest extends IndexTestCase
{
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
            self::assertStringContainsString('Foobar.php', $result->uri()->__toString());
            return;
        }

        $this->fail('Could not find interface');
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
            self::assertStringContainsString('Foobar.php', $result->uri()->__toString());
            return;
        }

        $this->fail('Could not find trait');
    }

    public function testSearcherForAttribute(): void
    {
        $this->workspace()->put('project/Bap.php', '<?php use Attribute as PHPAttribute; #[PHPAttribute] class Bap {}');
        $this->workspace()->put('project/Bar.php', '<?php #[\Attribute] class Bar {}');
        $this->workspace()->put('project/Baj.php', '<?php #[Attribute] class Baj {}');
        $this->workspace()->put('project/Foo/Bax.php', '<?php namespace Foo; use Not\Attribute; #[Attribute] class Bax {}');
        $this->workspace()->put('project/Baz.php', '<?php class Baz {}');
        $this->workspace()->put('project/Attribute/Bak.php', '<?php namespace Attribute; #[\Attribute] readonly class Bak {}');
        $agent = $this->indexAgent();
        $agent->indexer()->getJob()->run();
        $searcher = new IndexedNameSearcher($agent->search());

        $count = 0;

        foreach ($searcher->search('Ba', NameSearcherType::ATTRIBUTE) as $result) {
            assert($result instanceof NameSearchResult);
            self::assertContainsEquals($result->name()->head()->__toString(), ['Bar', 'Baj', 'Bap', 'Bak']);
            self::assertMatchesRegularExpression('#project(/Attribute)?/Ba[rjpk].php$#', $result->uri()->__toString());
            ++$count;
        }

        self::assertSame(4, $count);
    }
}
