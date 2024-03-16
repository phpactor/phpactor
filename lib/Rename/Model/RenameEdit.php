<?php

namespace Phpactor\Rename\Model;

use ArrayIterator;

/**
 * @extends ArrayIterator<LocatedTextEditsMap|RenameResult>
 */
class RenameEdit extends ArrayIterator
{
    public function __construct(
        LocatedTextEditsMap|RenameResult|null ...$edits,
    ) {
        parent::__construct(array_filter($edits));
    }
}
