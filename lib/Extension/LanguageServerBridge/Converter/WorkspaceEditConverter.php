<?php

namespace Phpactor\Extension\LanguageServerBridge\Converter;

use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\TextDocument\TextDocumentLocator;
use Phpactor\TextDocument\WorkspaceEdits;

class WorkspaceEditConverter
{
    public function __construct(private readonly TextDocumentLocator $locator)
    {
    }

    public function toLspWorkspaceEdit(WorkspaceEdits $edits): WorkspaceEdit
    {
        $lspEdits = [];
        foreach ($edits as $edit) {
            $lspEdits[$edit->uri()->__toString()] = TextEditConverter::toLspTextEdits(
                $edit->textEdits(),
                $this->locator->get($edit->uri())->__toString()
            );
        }
        return new WorkspaceEdit($lspEdits);
    }
}
