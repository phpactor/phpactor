<?php

namespace Phpactor\Indexer\Tests\Adapter\Parallel;

use Phpactor\Indexer\Adapter\Php\Serialized\FileRepository;
use Phpactor\Indexer\IndexAgentBuilder;
use Phpactor\Indexer\Model\IndexJob\ParallelIndexJob;
use Phpactor\Indexer\Model\Indexer;
use Phpactor\Indexer\Model\IndexJob\SerialIndexJob;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\RecordSerializer\PhpSerializer;
use Phpactor\Indexer\Tests\IntegrationTestCase;
use ReflectionObject;

class ParallelIndexJobTest extends IntegrationTestCase
{
    private const FILE_COUNT = 40;

    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->writeProject();
    }

    public function testOnlyIndexesInParallelWhenThereAreEnoughFiles(): void
    {
        self::assertInstanceOf(SerialIndexJob::class, $this->indexer(4, 'serial', 1000)->getJob());
        self::assertInstanceOf(ParallelIndexJob::class, $this->indexer(4, 'parallel', 1)->getJob());
    }

    public function testBuildsTheSameIndexAsIndexingInProcess(): void
    {
        $this->indexer(1, 'serial')->getJob()->run();
        $this->indexer(4, 'parallel')->getJob()->run();

        self::assertEquals($this->records('serial'), $this->records('parallel'));
    }

    private function indexer(int $workers, string $indexPath, int $minimumFiles = 1): Indexer
    {
        return IndexAgentBuilder::create(
            $this->workspace()->path($indexPath),
            $this->workspace()->path('project'),
        )
            ->setWorkerCommand([PHP_BINARY, __DIR__ . '/../../bin/console', 'index:worker'])
            ->setParallelWorkers($workers)
            ->setParallelMinimumFiles($minimumFiles)
            ->buildTestAgent()
            ->indexer();
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    private function records(string $indexPath): array
    {
        $repository = new FileRepository($this->workspace()->path($indexPath), new PhpSerializer());

        $records = [];
        foreach ($repository->iterator() as $record) {
            $records[$record->recordType() . '|' . $record->identifier()] = $this->normalise($record);
        }

        self::assertNotEmpty($records, 'index is empty');
        ksort($records);

        return $records;
    }

    /**
     * Records collect references and implementations in whatever order files
     * happen to be indexed in, which parallel indexing deliberately does not
     * preserve - compare them as sets.
     *
     * @return array<string,mixed>
     */
    private function normalise(Record $record): array
    {
        $data = [];

        foreach ((new ReflectionObject($record))->getProperties() as $property) {
            $value = $property->isInitialized($record) ? $property->getValue($record) : null;

            if (is_array($value)) {
                ksort($value);
            }

            $data[$property->getName()] = $value;
        }

        return $data;
    }

    private function writeProject(): void
    {
        $this->workspace()->put('project/src/Iface.php', <<<'PHP'
            <?php
            namespace Acme;
            interface Iface
            {
                public const IFACE_CONST = 1;
                public function run(): void;
            }
            PHP);

        $this->workspace()->put('project/src/BaseClass.php', <<<'PHP'
            <?php
            namespace Acme;
            abstract class BaseClass implements Iface
            {
                public function helper(): void
                {
                }
            }
            PHP);

        $this->workspace()->put('project/src/functions.php', <<<'PHP'
            <?php
            namespace Acme;
            function acme_helper(): void
            {
            }
            const ACME_VERSION = 1;
            PHP);

        $klass = <<<'PHP'
            <?php
            namespace Acme;
            class Klass%1$d extends BaseClass
            {
                public function run(): void
                {
                    $this->helper();
                    acme_helper();
                    echo Iface::IFACE_CONST . ACME_VERSION;
                    new Klass%2$d();
                }
            }
            PHP;

        for ($index = 0; $index < self::FILE_COUNT; $index++) {
            $this->workspace()->put(
                sprintf('project/src/Klass%d.php', $index),
                sprintf($klass, $index, ($index + 1) % self::FILE_COUNT)
            );
        }
    }
}
