<?php

namespace Phpactor\Indexer\Adapter\Php\Serialized;

use Phpactor\Indexer\Model\Index;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\Record\HasFileReferences;
use Phpactor\Indexer\Model\Record\HasPath;
use Phpactor\TextDocument\Exception\TextDocumentNotFound;
use Phpactor\TextDocument\TextDocumentLocator;
use Phpactor\TextDocument\TextDocumentUri;
use RuntimeException;
use SplFileInfo;

class SerializedIndex implements Index
{
    public function __construct(
        private FileRepository $repository,
        private TextDocumentLocator $locator
    ) {
    }

    public function lastUpdate(): int
    {
        return $this->repository->lastUpdate();
    }

    public function optimise(bool $dryRun): iterable
    {
        $count = 0;
        clearstatcache(true);
        foreach ($this->repository->iterator() as $cachePath => $record) {
            if ($record instanceof HasPath) {
                if (!$record->filePath()) {
                    yield sprintf(
                        'Record %s in %s has no file path: %s',
                        $record::class,
                        (string)$cachePath,
                        sprintf('%s:%s', $record->recordType(), $record->identifier())
                    );
                    if ($dryRun === false) {
                        $this->repository->remove($record);
                    }
                    continue;
                }

                try {
                    $doc = $this->locator->get(TextDocumentUri::fromString($record->filePath()));
                } catch (TextDocumentNotFound) {
                    if ($dryRun === false) {
                        $this->repository->remove($record);
                    }
                    yield sprintf(
                        'File does not exist so removed %s:%s',
                        $record->recordType(),
                        $record->identifier()
                    );
                    continue;
                }
            }
            if ($record instanceof HasFileReferences) {
                $removed = 0;
                foreach ($record->references() as $reference) {
                    try {
                        $doc = $this->locator->get(TextDocumentUri::fromString($reference));
                    } catch (TextDocumentNotFound) {
                        $removed++;
                        $record->removeReference($reference);
                    }
                }
                if ($removed > 0) {
                    $this->write($record);
                    yield sprintf(
                        'removed %d dead refernces from %s:%s',
                        $removed,
                        $record->recordType(),
                        $record->identifier(),
                    );
                }
            }

            $count++;

            // arbitrarily flush to disk after every N records
            if ($count % 500 === 0) {
                $this->repository->flush();
            }

            yield null;
        }

        $this->repository->flush();
    }

    public function get(Record $record): Record
    {
        return $this->repository->get($record) ?? $record;
    }

    public function write(Record $record): void
    {
        $this->repository->put($record);
    }

    public function isFresh(SplFileInfo $fileInfo): bool
    {
        try {
            $mtime = $fileInfo->getCTime();
        } catch (RuntimeException) {
            // file likely doesn't exist
            return false;
        }

        return $mtime < $this->lastUpdate();
    }

    public function reset(): void
    {
        $this->repository->reset();
    }

    public function exists(): bool
    {
        return $this->repository->lastUpdate() > 0;
    }

    public function done(): void
    {
        $this->repository->flush();
        $this->repository->putTimestamp();
    }

    public function has(Record $record): bool
    {
        return $this->repository->get($record) ? true : false;
    }
}
