<?php

namespace Phpactor\Indexer\Tests\Adapter;

use Phpactor\Indexer\Tests\IntegrationTestCase;
use function Safe\file_get_contents;

abstract class IndexTestCase extends IntegrationTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->workspace()->loadManifest(file_get_contents(__DIR__ . '/Manifest/buildIndex.php.test'));
    }
    public function testBuild(): void
    {
        $agent = $this->indexAgent();
        $agent->indexer()->getJob()->run();
        $references = $foo = $agent->query()->class()->implementing(
            'Index'
        );

        self::assertCount(2, $references);
    }
}
