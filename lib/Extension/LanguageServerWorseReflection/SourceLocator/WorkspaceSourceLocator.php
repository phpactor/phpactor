<?php

namespace Phpactor\Extension\LanguageServerWorseReflection\SourceLocator;

use Phpactor\Extension\LanguageServerWorseReflection\Workspace\WorkspaceIndex;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\SourceCodeLocator;

class WorkspaceSourceLocator implements SourceCodeLocator
{
    public function __construct(private WorkspaceIndex $index)
    {
    }


    public function locate(Name $name): SourceCode
    {
        if (null === $document = $this->index->documentForName($name)) {
            throw new SourceNotFound(sprintf(
                'Class "%s" not found',
                (string) $name
            ));
        }

        return SourceCode::fromUnknown($document);
    }
}
