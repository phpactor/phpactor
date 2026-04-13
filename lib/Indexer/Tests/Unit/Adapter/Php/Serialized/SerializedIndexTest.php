<?php

namespace Phpactor\Indexer\Tests\Unit\Adapter\Php\Serialized;

use PHPUnit\Framework\Assert;
use Phpactor\Indexer\Adapter\Php\Serialized\FileRepository;
use Phpactor\Indexer\Adapter\Php\Serialized\SerializedIndex;
use Phpactor\Indexer\Model\RecordSerializer\PhpSerializer;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\Indexer\Tests\IntegrationTestCase;
use Phpactor\TextDocument\FilesystemTextDocumentLocator;
use Phpactor\TextDocument\TextDocumentUri;
use SplFileInfo;

class SerializedIndexTest extends IntegrationTestCase
{
    public function testIsFreshWithNonExistingFile(): void
    {
        $repo = new FileRepository(
            $this->workspace()->path(),
            new PhpSerializer()
        );
        $index = new SerializedIndex(
            $repo,
            new FilesystemTextDocumentLocator(),
        );
        $info = new SplFileInfo($this->workspace()->path('no'));
        Assert::assertFalse($index->isFresh($info), 'File doesn\'t exist, so its not fresh');
    }

    public function testOptimizeWillRemoveRecordsWithNonExistingFiles(): void
    {
        $this->workspace()->reset();
        $this->workspace()->put('hello.php', '<?php echo "hello";');
        $this->workspace()->put('ref1.php', '<?php echo "hello";');
        $this->workspace()->put('ref2.php', '<?php echo "hello";');

        $repo = $this->createRepo();
        $index = $this->createIndex($repo);
        $index->write(
            ClassRecord::fromName('Foobar')->setFilePath(
                TextDocumentUri::fromString($this->workspace()->path('hello.php'))
            ),
        );
        $index->write(
            ClassRecord::fromName('Barfoo')->setFilePath(
                TextDocumentUri::fromString($this->workspace()->path('goodbye.php'))
            ),
        );
        $repo->flush();

        iterator_to_array($index->optimise(false));

        self::assertTrue($index->has(ClassRecord::fromName('Foobar')));
        self::assertFalse($index->has(ClassRecord::fromName('Barfoo')));
    }

    public function testOptimizeWillRemoveReferencesToNonExistingFiles(): void
    {
        $this->workspace()->reset();
        $this->workspace()->put('hello.php', '<?php echo "hello";');
        $this->workspace()->put('ref1.php', '<?php echo "hello";');
        $this->workspace()->put('ref2.php', '<?php echo "hello";');

        $repo = $this->createRepo();
        $index = $this->createIndex($repo);
        $index->write(
            ClassRecord::fromName('Foobar')->setFilePath(
                TextDocumentUri::fromString($this->workspace()->path('hello.php'))
            )->addReference(
                $this->workspace()->path('ref1.php'),
            )->addReference(
                $this->workspace()->path('ref2.php'),
            )->addReference(
                $this->workspace()->path('ref3.php'),
            ),
        );
        $repo->flush();

        iterator_to_array($index->optimise(false));

        $record = $index->get(ClassRecord::fromName('Foobar'));
        self::assertEquals([
            $this->workspace()->path('ref1.php'),
            $this->workspace()->path('ref2.php'),
        ], $record->references());
    }

    private function createRepo(): FileRepository
    {
        return new FileRepository($this->workspace()->path(), new PhpSerializer());
    }

    private function createIndex(FileRepository $repo): SerializedIndex
    {
        return new SerializedIndex(
            $repo,
            new FilesystemTextDocumentLocator(),
        );
    }
}
