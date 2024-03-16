<?php

namespace Phpactor\Indexer\Model\SearchIndex;

use Generator;
use PHPStan\Rules\UnusedFunctionParametersCheck;
use Phpactor\Indexer\Model\IndexAccess;
use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\Record\HasPath;
use Phpactor\Indexer\Model\SearchIndex;
use Psr\Log\LoggerInterface;

class ValidatingSearchIndex implements SearchIndex
{
    public function __construct(
        private SearchIndex $innerIndex,
        private IndexAccess $index,
        private LoggerInterface $logger
    ) {
    }


    public function search(Criteria $criteria): Generator
    {
        foreach ($this->innerIndex->search($criteria) as $result) {

            if (!$this->index->has($result)) {
                $this->innerIndex->remove($result);

                $this->logger->debug(sprintf(
                    'Record "%s" does not exist in index, removing from search',
                    $result->identifier()
                ));

                continue;
            }

            $record = $this->index->get($result);

            if (!$record instanceof HasPath) {
                yield $result;
                return;
            }

            if (!file_exists($record->filePath() ?? '')) {
                $this->innerIndex->remove($record);

                $this->logger->debug(sprintf(
                    'Record "%s" references non-existing file, removing from search index',
                    $record->identifier()
                ));

                continue;
            }

            yield $result;
        }

        $this->innerIndex->flush();
    }

    public function write(Record $record): void
    {
        $this->innerIndex->write($record);
    }

    public function remove(Record $record): void
    {
        $this->innerIndex->remove($record);
    }

    public function flush(): void
    {
        $this->innerIndex->flush();
    }
}
