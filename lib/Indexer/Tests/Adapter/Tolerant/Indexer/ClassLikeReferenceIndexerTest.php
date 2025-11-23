<?php

namespace Phpactor\Indexer\Tests\Adapter\Tolerant\Indexer;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\Indexer\Adapter\Tolerant\Indexer\ClassLikeReferenceIndexer;
use Phpactor\Indexer\Model\LocationConfidence;
use Phpactor\Indexer\Tests\Adapter\Tolerant\TolerantIndexerTestCase;

class ClassLikeReferenceIndexerTest extends TolerantIndexerTestCase
{
    /**
     * @param array{int,int,int} $expectedCounts
     */
    #[DataProvider('provideClasses')]
    public function testMembers(string $manifest, string $fqn, array $expectedCounts): void
    {
        $this->workspace()->reset();
        $this->workspace()->loadManifest($manifest);
        $agent = $this->runIndexer(new ClassLikeReferenceIndexer(), 'src');

        $counts = [
            LocationConfidence::CONFIDENCE_NOT => 0,
            LocationConfidence::CONFIDENCE_MAYBE => 0,
            LocationConfidence::CONFIDENCE_SURELY => 0,
        ];

        foreach ($agent->query()->class()->referencesTo($fqn) as $locationConfidence) {
            $counts[$locationConfidence->__toString()]++;
        }

        self::assertEquals(array_combine(array_keys($counts), $expectedCounts), $counts);
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideClasses(): Generator
    {
        yield 'single ref' => [
            "// File: src/file1.php\n<?php Foobar::bar()",
            'Foobar',
            [0, 0, 1]
        ];

        yield 'multiple ref' => [
            "// File: src/file1.php\n<?php Foobar::static(); Foobar::static()",
            'Foobar',
            [0, 0, 2]
        ];

        yield 'constant access' => [
            "// File: src/file1.php\n<?php Foobar::class;",
            'Foobar',
            [0, 0, 1]
        ];

        yield 'class extends' => [
            "// File: src/file1.php\n<?php class Barfoo extends Foobar {};",
            'Foobar',
            [0, 0, 1]
        ];

        yield 'class implements' => [
            "// File: src/file1.php\n<?php class Barfoo implements Foobar {};",
            'Foobar',
            [0, 0, 1]
        ];

        yield 'class multiple implements' => [
            "// File: src/file1.php\n<?php class Barfoo implements Foobar,Barfoo {};",
            'Foobar',
            [0, 0, 1]
        ];

        yield 'class multiple implements 2' => [
            "// File: src/file1.php\n<?php class Barfoo implements Foo,Bar {};",
            'Bar',
            [0, 0, 1]
        ];

        yield 'abstract class implements' => [
            "// File: src/file1.php\n<?php abstract class Barfoo implements Foobar,Barfoo {};",
            'Foobar',
            [0, 0, 1]
        ];

        yield 'use trait (basic)' => [
            "// File: src/file1.php\n<?php class C { use T; }",
            'T',
            [0, 0, 1]
        ];

        yield 'use trait (namespaced)' => [
            "// File: src/file1.php\n<?php use N\T; class C { use T; }",
            'N\T',
            [0, 0, 1]
        ];
    }
}
