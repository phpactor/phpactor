<?php

namespace Phpactor\Rename\Model;

use ArrayIterator;

/**
 * @extends ArrayIterator<int, LocatedTextEditsMap|RenameResult>
 */
class WorkspaceRenameEdit extends ArrayIterator
{
    public function __construct(LocatedTextEditsMap|RenameResult|null ...$edits)
    {
        assert(array_is_list($edits));

        parent::__construct(array_filter($edits));
    }
}
