<?php

namespace Phpactor\Indexer\Tests\Adapter\Tolerant\Indexer;

use Generator;
use Phpactor\Extension\PHPUnit\PHPUnitExtension;
use Phpactor\Indexer\Adapter\Tolerant\Indexer\ClassDeclarationIndexer;
use Phpactor\Indexer\Adapter\Tolerant\Indexer\EnumDeclarationIndexer;
use Phpactor\Indexer\Adapter\Tolerant\Indexer\InterfaceDeclarationIndexer;
use Phpactor\Indexer\Adapter\Tolerant\Indexer\TraitDeclarationIndexer;
use Phpactor\Indexer\Model\Query\Criteria\ExactShortName;
use Phpactor\Indexer\Model\Query\Criteria\ShortNameBeginsWith;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\Indexer\Tests\Adapter\Tolerant\TolerantIndexerTestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use RuntimeException;

class ClassLikeDeclarationIndexerTest extends TolerantIndexerTestCase
{
    use \Prophecy\PhpUnit\ProphecyTrait;

    /**
     * @dataProvider provideImplementations
     */
    public function testImplementations(string $manifest, string $fqn, int $expectedCount): void
    {
        $this->workspace()->reset();
        $this->workspace()->loadManifest($manifest);

        $agent = $this->indexAgentBuilder('src')
            ->setIndexers([
                new ClassDeclarationIndexer(),
            ])->buildAgent();

        $agent->indexer()->getJob()->run();

        self::assertCount($expectedCount, $agent->query()->class()->implementing($fqn));
    }

    /**
     * @return Generator<mixed>
     */
    public function provideImplementations(): Generator
    {
        yield 'no implementations' => [
            "// File: src/file1.php\n<?php class Barfoo {}",
            'Foobar',
            0
        ];

        yield 'class implements' => [
            "// File: src/file1.php\n<?php class Barfoo implements Foobar{}",
            'Foobar',
            1
        ];

        yield 'class implements multiple' => [
            "// File: src/file1.php\n<?php class Barfoo implements Baz, Foobar{}",
            'Foobar',
            1
        ];

        yield 'abstract class implements' => [
            "// File: src/file1.php\n<?php abstract class Barfoo implements Foobar{}",
            'Foobar',
            1
        ];
    }

    /**
     * @dataProvider provideSearch
     * @param array<ClassRecord> $expectedRecords
     */
    public function testSearch(string $manifest, string $search, array $expectedRecords): void
    {
        $this->workspace()->reset();
        $this->workspace()->loadManifest($manifest);
        $agent = $this->runIndexer(
            [
                new ClassDeclarationIndexer(),
                new EnumDeclarationIndexer(),
                new InterfaceDeclarationIndexer(),
                new TraitDeclarationIndexer(),
            ],
            'src'
        );
        $foundRecords = $agent->search()->search(new ShortNameBeginsWith($search));

        if (empty($expectedRecords)) {
            self::assertCount(0, iterator_to_array($foundRecords));
            return;
        }

        foreach ($expectedRecords as $record) {
            foreach ($foundRecords as $foundRecord) {
                assert($foundRecord instanceof ClassRecord);
                if ($foundRecord->identifier() === $record->identifier()) {
                    self::assertEquals($record->filePath(), $foundRecord->filePath());
                    continue 2;
                }
            }

            throw new RuntimeException(sprintf(
                'Record "%s" not found',
                $record->identifier()
            ));
        }

        $this->addToAssertionCount(1);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideSearch(): Generator
    {
        yield 'no results' => [
            "// File: src/file1.php\n<?php class Barfoo {}",
            'Foobar',
            []
        ];

        yield 'exact match' => [
            "// File: src/file1.php\n<?php class Barfoo implements Foobar{}",
            'Barfoo',
            [ClassRecord::fromName('Barfoo')->setFilePath($this->workspace()->path('src/file1.php'))]
        ];

        yield 'namespaced match' => [
            "// File: src/file1.php\n<?php namespace Bar; class Barfoo implements Foobar{}",
            'Barfoo',
            [ClassRecord::fromName('Bar\Barfoo')->setFilePath($this->workspace()->path('src/file1.php'))]
        ];

        yield 'gh-2098: does not index reserved class name' => [
            file_get_contents(__DIR__ . '/fixture/gh-2098.test'),
            'Query',
            [],
        ];
    }

    public function testSearchMultipleResults(): void
    {
        $manifest = <<<PHP
            // File: src/file1.php
            <?php class Barfoo{}
            // File: src/file2.php
            <?php

            class Barfoo{}
            PHP;
        $this->workspace()->reset();
        $this->workspace()->loadManifest($manifest);
        $agent = $this->runIndexer(
            [
                new ClassDeclarationIndexer(),
            ],
            'src'
        );

        $foundRecords = iterator_to_array($agent->search()->search(new ExactShortName('Barfoo')));

        self::assertCount(2, $foundRecords);

        $firstMatch = $foundRecords[0];
        self::assertEquals('Barfoo', $firstMatch->identifier());
        self::assertInstanceOf(ClassRecord::class, $firstMatch);
        self::assertEquals($this->workspace()->path('src/file1.php'), $firstMatch->filePath());

        $secondMatch = $foundRecords[1];
        self::assertEquals('Barfoo', $secondMatch->identifier());
        self::assertInstanceOf(ClassRecord::class, $secondMatch);
        self::assertEquals($this->workspace()->path('src/file2.php'), $secondMatch->filePath());
    }

    /**
     * @dataProvider provideInvalidClasses
     */
    public function testInvalidClass(string $manifest, string $exectedMessage): void
    {
        $this->workspace()->reset();
        $this->workspace()->loadManifest($manifest);

        $logger = $this->prophesize(LoggerInterface::class);

        $logger->warning(Argument::containingString($exectedMessage))->shouldBeCalled();

        $agent = $this->indexAgentBuilder('src')
            ->setIndexers([
                new ClassDeclarationIndexer(),
            ])->setLogger($logger->reveal())->buildAgent();

        $agent->indexer()->getJob()->run();
        $this->addToAssertionCount(1);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideInvalidClasses(): Generator
    {
        yield 'no class name' => [
            "// File: src/file1.php\n<?php class {}",
            'Class name is missing',
        ];
    }
}
