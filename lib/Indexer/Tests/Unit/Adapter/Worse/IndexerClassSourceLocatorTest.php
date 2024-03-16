<?php

namespace Phpactor\Indexer\Tests\Unit\Adapter\Worse;

use PHPStan\Command\AnalyseApplication;
use PHPUnit\Framework\TestCase;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\Indexer\Adapter\Php\InMemory\InMemoryIndex;
use Phpactor\Indexer\Adapter\Worse\IndexerClassSourceLocator;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;
use Phpactor\WorseReflection\Core\Name;
use Symfony\Component\Filesystem\Path;

class IndexerClassSourceLocatorTest extends TestCase
{
    public function testThrowsExceptionIfClassNotInIndex(): void
    {
        $this->expectException(SourceNotFound::class);
        $index = new InMemoryIndex();
        $locator = $this->createLocator($index);
        $locator->locate(Name::fromString('Foobar'));
    }

    public function testThrowsExceptionIfFileDoesNotExist(): void
    {
        $this->expectException(SourceNotFound::class);
        $this->expectExceptionMessage('does not exist');
        $record = ClassRecord::fromName('Foobar')
            ->setType('class')
            ->setStart(ByteOffset::fromInt(0))
            ->setEnd(ByteOffset::fromInt(0))
            ->setFilePath('nope.php');

        $index = new InMemoryIndex();
        $index->write($record);
        $locator = $this->createLocator($index);
        $locator->locate(Name::fromString('Foobar'));
    }

    public function testReturnsSourceCode(): void
    {
        $record = ClassRecord::fromName('Foobar')
            ->setType('class')
            ->setStart(ByteOffset::fromInt(0))
            ->setEnd(ByteOffset::fromInt(10))
            ->setFilePath(__FILE__);

        $index = new InMemoryIndex();
        $index->write($record);
        $locator = $this->createLocator($index);
        $sourceCode = $locator->locate(Name::fromString('Foobar'));
        $this->assertEquals(Path::canonicalize(__FILE__), $sourceCode->uri()?->path());
    }

    public function testForPhar(): void
    {
        $record = ClassRecord::fromName('Foobar')
            ->setType('class')
            ->setStart(ByteOffset::fromInt(0))
            ->setEnd(ByteOffset::fromInt(10))
            ->setFilePath('phar://' . __FILE__);

        $index = new InMemoryIndex();
        $index->write($record);
        $locator = $this->createLocator($index);
        $sourceCode = $locator->locate(Name::fromString('Foobar'));
        $this->assertEquals(Path::canonicalize(__FILE__), $sourceCode->uri()?->path());
    }

    private function createLocator(InMemoryIndex $index): IndexerClassSourceLocator
    {
        return new IndexerClassSourceLocator($index);
    }
}
