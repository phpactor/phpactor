<?php

namespace Phpactor\Extension\LanguageServerWorseReflection\SourceLocator;

use Phpactor\Extension\LanguageServerWorseReflection\Workspace\WorkspaceIndex;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\SourceCodeLocator;

class WorkspaceSourceLocator implements SourceCodeLocator
{
    public function __construct(private readonly WorkspaceIndex $index)
    {
    }


    public function locate(Name $name): TextDocument
    {
        if (null === $document = $this->index->documentForName($name)) {
            throw new SourceNotFound(sprintf(
                'Class "%s" not found',
                (string) $name
            ));
        }

        return $document;
    }
}
