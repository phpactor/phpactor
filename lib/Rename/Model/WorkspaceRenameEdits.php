<?php

namespace Phpactor\Rename\Model;

use ArrayIterator;

/**
 * @extends ArrayIterator<int, LocatedTextEditsMap|RenameResult>
 */
class WorkspaceRenameEdits extends ArrayIterator
{
    /**
     * @param list<LocatedTextEditsMap|RenameResult> $edits
     */
    public function __construct(array $edits)
    {
        assert(array_is_list($edits));

        parent::__construct(array_filter($edits));
    }
}
