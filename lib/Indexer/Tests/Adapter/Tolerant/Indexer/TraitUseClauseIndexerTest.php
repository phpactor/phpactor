<?php

namespace Phpactor\Indexer\Tests\Adapter\Tolerant\Indexer;

use Generator;
use Phpactor\Indexer\Adapter\Tolerant\Indexer\TraitUseClauseIndexer;
use Phpactor\Indexer\Tests\Adapter\Tolerant\TolerantIndexerTestCase;

class TraitUseClauseIndexerTest extends TolerantIndexerTestCase
{
    /**
    * @dataProvider provideImplementations
    */
    public function testImplementations(string $manifest, string $fqn, int $expectedCount): void
    {
        $this->workspace()->reset();
        $this->workspace()->loadManifest($manifest);
        $agent = $this->runIndexer(new TraitUseClauseIndexer(), 'src');
        self::assertEquals($expectedCount, count($agent->query()->class()->implementing($fqn)));
    }

    /**
    * @return Generator<mixed>
    */
    public function provideImplementations(): Generator
    {
        yield 'use trait (basic)' => [
            "// File: src/file1.php\n<?php namespace N; use T; class C { use T; }",
            'T',
            1
        ];

        yield 'use trait (namespaced)' => [
            "// File: src/file1.php\n<?php use N\T; class C { use T; }",
            'N\T',
            1
        ];

        yield 'use trait (namespaced & grouped)' => [
            "// File: src/file1.php\n<?php use N\{T}; class C { use T; }",
            'N\T',
            1
        ];

        yield 'use trait (aliased)' => [
            "// File: src/file1.php\n<?php use N\T as G; class C { use G; }",
            'N\T',
            1
        ];

        // Adding a test for 8.1 and higher to also index Enums
        yield 'use trait (enum)' => [
            "// File: src/file1.php\n<?php enum C { use T; }",
            'T',
            1,
        ];
    }
}
