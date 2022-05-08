<?php

namespace Phpactor\Indexer\Tests\Adapter\ReferenceFinder;

use Phpactor\Indexer\Adapter\ReferenceFinder\IndexedNameSearcher;
use Phpactor\Indexer\Tests\Adapter\IndexTestCase;
use Phpactor\ReferenceFinder\NameSearcherType;
use Phpactor\ReferenceFinder\Search\NameSearchResult;

class IndexedNameSearcherTest extends IndexTestCase
{
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

        $this->fail('Could not find interface');
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

        $this->fail('Could not find interface');
    }
}
