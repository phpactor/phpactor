<?php

namespace Phpactor\Indexer\Adapter\Php;

use Generator;
use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\RecordFactory;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\Indexer\Model\SearchIndex;
use Safe\Exceptions\FilesystemException;
use function Safe\file_get_contents;
use function Safe\file_put_contents;

class FileSearchIndex implements SearchIndex
{
    /**
     * Flush to the filesystem after BATCH_SIZE updates
     */
    private const BATCH_SIZE = 10000;

    private bool $initialized = false;

    /**
     * @var array<FileIndexEntry> $subjects
     */
    private array $subjects = [];

    private int $counter = 0;

    private bool $dirty = false;

    public function __construct(private string $path)
    {
    }

    public function search(Criteria $criteria): Generator
    {
        $this->open();

        foreach ($this->subjects as $subject) {
            $record = RecordFactory::create($subject->recordType(), $subject->identifier());
            if ($record instanceof ClassRecord) {
                $record = $record->withType($subject->classType());
                $record->setFlags($subject->flags());
            }

            if (false === $criteria->isSatisfiedBy($record)) {
                continue;
            }

            yield $record;
        }
    }

    public function write(Record $record): void
    {
        $this->open();
        $this->subjects[$this->recordHash($record)] = FileIndexEntry::fromRecord($record);
        $this->dirty = true;

        if (++$this->counter % self::BATCH_SIZE === 0) {
            $this->flush();
        }
    }

    public function remove(Record $record): void
    {
        unset($this->subjects[$this->recordHash($record)]);
        $this->dirty = true;
    }

    public function flush(): void
    {
        if (false === $this->dirty) {
            return;
        }

        $this->open();

        $content = implode("\n", array_unique(
            array_map(fn (FileIndexEntry $subject) => $subject->__toString(), $this->subjects)
        ));

        try {
            file_put_contents($this->path, $content);
        } catch (FilesystemException $e) {
            if (file_exists(dirname($this->path))) {
                throw $e;
            }

            mkdir(dirname($this->path), 0777, true);
            file_put_contents($this->path, $content);
        }

        $this->dirty = false;
    }

    private function open(): void
    {
        if ($this->initialized) {
            return;
        }

        if (!file_exists($this->path)) {
            return;
        }

        $this->subjects = array_filter(array_map(
            FileIndexEntry::fromString(...),
            explode("\n", file_get_contents($this->path))
        ));

        $this->initialized = true;
    }

    private function recordHash(Record $record): string
    {
        return $record->recordType()->value.$record->identifier();
    }
}
