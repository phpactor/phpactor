<?php

namespace Phpactor\Indexer\Model;

use RuntimeException;

interface IndexAccess
{
    /**
     * Return the indexed version of Record, if it doesn't exist in the index,
     * it should return the given record.
     *
     * If the record is of an unknown type (e.g. not ClassRecord or FunctionRecord)
     * then an exception will be thrown.
     *
     * @throws RuntimeException
     *
     * @template TRecord of \Phpactor\Indexer\Model\Record
     * @param TRecord $record
     *
     * @return TRecord
     */
    public function get(Record $record): Record;

    public function has(Record $record): bool;
}
