<?php

namespace Phpactor\Indexer\Tests\Unit\Adapter\Php\Serialized;

use Prophecy\PhpUnit\ProphecyTrait;
use Phpactor\Indexer\Adapter\Php\Serialized\FileRepository;
use Phpactor\Indexer\Model\Exception\CorruptedRecord;
use Phpactor\Indexer\Model\RecordSerializer;
use Phpactor\Indexer\Model\RecordSerializer\PhpSerializer;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\Indexer\Tests\IntegrationTestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class FileRepositoryTest extends IntegrationTestCase
{
    use ProphecyTrait;

    public function testResetRemovesTheIndex(): void
    {
        $repo = $this->createFileRepository();
        $this->workspace()->put('index/something.cache', 'foo');
        $this->workspace()->put('index/something/else/some.cache', 'bar');

        self::assertFileExists($this->workspace()->path('index/something.cache'));
        self::assertFileExists($this->workspace()->path('index/something/else/some.cache'));

        $repo->reset();

        self::assertFileDoesNotExist($this->workspace()->path('index/something.cache'));
        self::assertFileDoesNotExist($this->workspace()->path('index/something/else/some.cache'));
    }

    public function testRemovesClassRecord(): void
    {
        $repo = $this->createFileRepository();
        $repo->put(ClassRecord::fromName('Foobar'));
        $repo->flush();
        self::assertNotNull($repo->get(ClassRecord::fromName('Foobar')));
        $repo->remove(ClassRecord::fromName('Foobar'));
        self::assertNull($repo->get(ClassRecord::fromName('Foobar')));
    }

    public function testLogsCorruptedRecordError(): void
    {
        $serialized = $this->prophesize(RecordSerializer::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $serialized->deserialize(Argument::any())->willThrow(
            new CorruptedRecord('no')
        );

        $serialized->serialize(Argument::any())->willReturn('foo');

        $repo = new FileRepository(
            $this->workspace()->path('index'),
            $serialized->reveal(),
            $logger->reveal()
        );

        $repo->put(ClassRecord::fromName('Foo'));
        $repo->flush();
        $repo->get(ClassRecord::fromName('Foo'));

        $logger->warning(Argument::containingString('corrupted'))->shouldHaveBeenCalled();
    }

    private function createFileRepository(): FileRepository
    {
        return new FileRepository(
            $this->workspace()->path('index'),
            new PhpSerializer()
        );
    }
}
