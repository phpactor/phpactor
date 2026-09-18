<?php

namespace Phpactor\Indexer\Tests\Unit\Model;

use Phpactor\Indexer\Adapter\Php\InMemory\InMemoryIndex;
use Phpactor\Indexer\Model\Name\FullyQualifiedName;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\Indexer\Model\Record\FileRecord;
use Phpactor\Indexer\Model\Record\MemberRecord;
use Phpactor\Indexer\Model\RecordMerger;
use Phpactor\Indexer\Model\RecordReference;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentUri;
use PHPUnit\Framework\TestCase;

class RecordMergerTest extends TestCase
{
    public function testWritesRecordsTheIndexDoesNotHaveYet(): void
    {
        $index = new InMemoryIndex();

        $incoming = ClassRecord::fromName('Foobar');
        $incoming->addReference('/one.php');

        (new RecordMerger())->merge($index, $incoming);

        $record = $index->get(ClassRecord::fromName('Foobar'));
        self::assertEquals(['/one.php'], $record->references());
    }

    public function testUnionsReferencesContributedByDifferentWorkers(): void
    {
        $index = new InMemoryIndex();
        $merger = new RecordMerger();

        $merger->merge($index, ClassRecord::fromName('Foobar')->addReference('/one.php'));
        $merger->merge($index, ClassRecord::fromName('Foobar')->addReference('/two.php'));

        $record = $index->get(ClassRecord::fromName('Foobar'));
        self::assertEquals(['/one.php', '/two.php'], $record->references());
    }

    public function testKeepsTheDeclarationWhenMergingARecordThatOnlyReferencesIt(): void
    {
        $index = new InMemoryIndex();
        $merger = new RecordMerger();

        $declaration = ClassRecord::fromName('Foobar');
        $declaration->setType(ClassRecord::TYPE_INTERFACE);
        $declaration->setFilePath(TextDocumentUri::fromString('/foobar.php'));
        $declaration->setStart(ByteOffset::fromInt(10));
        $declaration->setEnd(ByteOffset::fromInt(20));
        $merger->merge($index, $declaration);

        $merger->merge($index, ClassRecord::fromName('Foobar')->addReference('/other.php'));

        $record = $index->get(ClassRecord::fromName('Foobar'));
        self::assertEquals('file:///foobar.php', $record->filePath());
        self::assertEquals(ClassRecord::TYPE_INTERFACE, $record->type());
        self::assertEquals(10, $record->start()->toInt());
        self::assertEquals(['/other.php'], $record->references());
    }

    public function testADeclarationReplacesTheOneAlreadyInTheIndex(): void
    {
        $index = new InMemoryIndex();
        $merger = new RecordMerger();

        $first = ClassRecord::fromName('Foobar');
        $first->setFilePath(TextDocumentUri::fromString('/one.php'));
        $first->setStart(ByteOffset::fromInt(1));
        $first->setEnd(ByteOffset::fromInt(2));
        $first->addImplements(FullyQualifiedName::fromString('One'));
        $merger->merge($index, $first);

        $second = ClassRecord::fromName('Foobar');
        $second->setFilePath(TextDocumentUri::fromString('/two.php'));
        $second->setStart(ByteOffset::fromInt(3));
        $second->setEnd(ByteOffset::fromInt(4));
        $second->addImplements(FullyQualifiedName::fromString('Two'));
        $merger->merge($index, $second);

        $record = $index->get(ClassRecord::fromName('Foobar'));
        self::assertEquals('file:///two.php', $record->filePath());
        self::assertEquals(['Two' => 'Two'], $record->implements());
    }

    public function testUnionsImplementations(): void
    {
        $index = new InMemoryIndex();
        $merger = new RecordMerger();

        $first = ClassRecord::fromName('Iface');
        $first->addImplementation(FullyQualifiedName::fromString('One'));
        $merger->merge($index, $first);

        $second = ClassRecord::fromName('Iface');
        $second->addImplementation(FullyQualifiedName::fromString('Two'));
        $merger->merge($index, $second);

        $record = $index->get(ClassRecord::fromName('Iface'));
        self::assertEquals(['One' => 'One', 'Two' => 'Two'], $record->implementations());
    }

    public function testFileRecordsReplaceWhatTheIndexHolds(): void
    {
        $index = new InMemoryIndex();
        $merger = new RecordMerger();

        $before = FileRecord::fromPath('/one.php');
        $before->addReference(new RecordReference(ClassRecord::RECORD_TYPE, 'Old', 1));
        $merger->merge($index, $before);

        $after = FileRecord::fromPath('/one.php');
        $after->addReference(new RecordReference(ClassRecord::RECORD_TYPE, 'New', 1));
        $merger->merge($index, $after);

        $record = $index->get(FileRecord::fromPath('/one.php'));
        self::assertEquals(['New'], array_map(
            fn (RecordReference $reference): string => $reference->identifier(),
            $record->references()->toArray()
        ));
    }

    public function testUnionsMemberReferences(): void
    {
        $index = new InMemoryIndex();
        $merger = new RecordMerger();

        $merger->merge($index, MemberRecord::fromIdentifier('method#foo')->addReference('/one.php'));
        $merger->merge($index, MemberRecord::fromIdentifier('method#foo')->addReference('/two.php'));

        $record = $index->get(MemberRecord::fromIdentifier('method#foo'));
        self::assertEquals(['/one.php', '/two.php'], $record->references());
    }
}
