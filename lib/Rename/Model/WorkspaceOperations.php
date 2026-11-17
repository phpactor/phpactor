<?php

namespace Phpactor\Rename\Model;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<LocatedTextEdits|RenameResult>
 */
final class WorkspaceOperations implements IteratorAggregate
{
    /**
     * @param array<LocatedTextEdits|RenameResult> $edits
     */
    public function __construct(private array $edits)
    {
    }

    public function merge(WorkspaceOperations $workspaceEdits): self
    {
        return new self(array_merge($this->edits, $workspaceEdits->edits));
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->edits);
    }
}
