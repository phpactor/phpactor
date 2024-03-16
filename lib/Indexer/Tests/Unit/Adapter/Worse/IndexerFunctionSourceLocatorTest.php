<?php

namespace Phpactor\Indexer\Tests\Unit\Adapter\Worse;

use PHPUnit\Framework\TestCase;
use Phpactor\Indexer\Model\Name\FullyQualifiedName;
use Phpactor\Indexer\Adapter\Php\InMemory\InMemoryIndex;
use Phpactor\Indexer\Adapter\Worse\IndexerFunctionSourceLocator;
use Phpactor\Indexer\Model\Record\FunctionRecord;
use Phpactor\TextDocument\TextDocumentUri;
use Symfony\Component\Filesystem\Path;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;
use Phpactor\WorseReflection\Core\Name;

class IndexerFunctionSourceLocatorTest extends TestCase
{
    public function testThrowsExceptionIfFunctionNotInIndex(): void
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
        $record = new FunctionRecord(
            FullyQualifiedName::fromString('Foobar')
        );
        $record->setFilePath(TextDocumentUri::fromString('/nope.php'));
        $index = new InMemoryIndex();
        $index->write($record);
        $locator = $this->createLocator($index);
        $locator->locate(Name::fromString('Foobar'));
    }

    public function testReturnsSourceCode(): void
    {
        $record = new FunctionRecord(
            FullyQualifiedName::fromString('Foobar')
        );
        $record->setFilePath(TextDocumentUri::fromString(__FILE__));
        $index = new InMemoryIndex();
        $index->write($record);
        $locator = $this->createLocator($index);
        $sourceCode = $locator->locate(Name::fromString('Foobar'));
        $this->assertEquals(Path::canonicalize(__FILE__), $sourceCode->uri()?->path());
    }

    private function createLocator(InMemoryIndex $index): IndexerFunctionSourceLocator
    {
        return new IndexerFunctionSourceLocator($index);
    }
}
