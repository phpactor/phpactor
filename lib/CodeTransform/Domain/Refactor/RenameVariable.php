<?php

namespace Phpactor\CodeTransform\Domain\Refactor;

use Phpactor\CodeTransform\Domain\SourceCode;

interface RenameVariable
{
    const SCOPE_LOCAL = 'local';
    const SCOPE_FILE = 'file';

    public function renameVariable(SourceCode $source, int $offset, string $newName, string $scope = RenameVariable::SCOPE_FILE): SourceCode;
}
