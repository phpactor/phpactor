<?php

namespace Phpactor\Rename\Tests\Adapter\ClassMover;

use Phpactor\ClassFileConverter\Adapter\Simple\SimpleFileToClass;
use Phpactor\ClassMover\ClassMover;
use Phpactor\Indexer\Model\Record;
use Phpactor\Rename\Adapter\ClassMover\FileRenamer;
use Phpactor\Rename\Adapter\ClassToFile\ClassToFileUriToNameConverter;
use Phpactor\Rename\Model\LocatedTextEditsMap;
use Phpactor\Extension\LanguageServerRename\Tests\IntegrationTestCase;
use Phpactor\Indexer\Adapter\Php\InMemory\InMemoryIndex;
use Phpactor\Indexer\Model\QueryClient;
use Phpactor\Indexer\Model\RecordReference;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\Indexer\Model\Record\FileRecord;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\TextDocument\TextDocumentLocator\InMemoryDocumentLocator;
use Phpactor\TextDocument\TextDocumentUri;
use function Amp\Promise\wait;

class FileRenamerTest extends IntegrationTestCase
{
    /**
     * Tests that the definition and the references are renamed.  Note that we
     * use the "Simple" file-to-class name strategy which scans the contents of
     * the destination file to get the class name. In reality we would use
     * Composer to do this. This is why the destination file has already been
     * renamed in the test.
     */
    public function testRenameReferencesInTwoFiles(): void
    {
        $document1 = $this->createDocument('1.php', '<?php class One{}');
        $document2 = $this->createDocument('2.php', '<?php class Two{}; One::class;');
        $document3 = $this->createDocument('3.php', '<?php One::class;');
        $document4 = $this->createDocument('4.php', '<?php One::class;');

        $renamer = $this->createRenamer(
            [$document1, $document2, $document3, $document4],
            [
                (new ClassRecord('One'))
                    ->setType('class')
                    ->addReference((string)TextDocumentUri::fromString($this->path('3.php')))
                    ->addReference((string)TextDocumentUri::fromString($this->path('4.php'))),
                FileRecord::fromPath((string)TextDocumentUri::fromString($this->path('3.php')))
                    ->addReference(new RecordReference(ClassRecord::RECORD_TYPE, 'One', 10, end: 20)),
                FileRecord::fromPath((string)TextDocumentUri::fromString($this->path('4.php')))
                    ->addReference(new RecordReference(ClassRecord::RECORD_TYPE, 'One', 10, end: 20)),
           ]
        );

        $edits = wait($renamer->renameFile($document1->uriOrThrow(), $document2->uriOrThrow()));

        self::assertInstanceOf(LocatedTextEditsMap::class, $edits);
        assert($edits instanceof LocatedTextEditsMap);
        self::assertCount(3, $edits->toLocatedTextEdits(), 'Locates two references');
    }

    /**
     * Tests that the definition is renamed on the original file as described
     * in the LSP spec. Note that we use the "Simple" file-to-class name
     * strategy which scans the contents of the destination file to get the
     * class name. Therefore, despite having to save the renamed file, we do
     * not add it to the InMemoryDocumentLocator to simulate the behavior when
     * using Composer that has no reference to the new file, only the old one.
     */
    public function testRenameReferencesToNewFile(): void
    {
        $document1 = $this->createDocument('One.php', '<?php class One{}');
        $document2 = $this->createDocument('Two.php', '<?php class Two{}; One::class;');

        file_put_contents($document2->uri()->path(), $document2->__toString());
        $renamer = $this->createRenamer([$document1], [(new ClassRecord('One'))->setType('class')]);

        $edits = wait($renamer->renameFile($document1->uri(), $document2->uri()));

        self::assertInstanceOf(LocatedTextEditsMap::class, $edits);
        assert($edits instanceof LocatedTextEditsMap);
        self::assertCount(1, $edits->toLocatedTextEdits(), 'Locates one reference');
    }

    /**
     * @param TextDocument[] $textDocuments
     * @param Record[] $records
     */
    private function createRenamer(array $textDocuments, array $records): FileRenamer
    {
        foreach ($textDocuments as $textDocument) {
            assert($textDocument instanceof TextDocument);
            file_put_contents($textDocument->uri()->path(), $textDocument->__toString());
        }

        return new FileRenamer(
            new ClassToFileUriToNameConverter(new SimpleFileToClass()),
            InMemoryDocumentLocator::fromTextDocuments($textDocuments),
            new QueryClient(new InMemoryIndex($records)),
            new ClassMover(),
        );
    }

    private function path(string $path): string
    {
        return $this->workspace()->path($path);
    }

    private function createDocument(string $path, string $content): TextDocument
    {
        return TextDocumentBuilder::create($content)->uri($this->path($path))->build();
    }
}
