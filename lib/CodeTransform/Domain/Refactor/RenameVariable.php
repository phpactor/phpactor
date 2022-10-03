<?php

namespace Phpactor\CodeTransform\Domain\Refactor;

use Phpactor\CodeTransform\Domain\SourceCode;

interface RenameVariable
{
    public const SCOPE_LOCAL = 'local';
    public const SCOPE_FILE = 'file';

    public function renameVariable(SourceCode $source, int $offset, string $newName, string $scope = RenameVariable::SCOPE_FILE): SourceCode;
}
