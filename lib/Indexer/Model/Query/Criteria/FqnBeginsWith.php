<?php

namespace Phpactor\Indexer\Model\Query\Criteria;

use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\Record\HasFullyQualifiedName;

class FqnBeginsWith extends Criteria
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function isSatisfiedBy(Record $record): bool
    {
        if (!$this->name) {
            return false;
        }

        if (!$record instanceof HasFullyQualifiedName) {
            return false;
        }

        return 0 === strpos($record->fqn()->__toString(), $this->name);
    }
}
