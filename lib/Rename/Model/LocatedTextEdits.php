<?php

namespace Phpactor\Rename\Model;

use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\TextEdits;

class LocatedTextEdits
{
    public function __construct(
        private readonly TextEdits $textEdits,
        private readonly TextDocumentUri $documentUri
    ) {
    }

    public function textEdits(): TextEdits
    {
        return $this->textEdits;
    }

    public function documentUri(): TextDocumentUri
    {
        return $this->documentUri;
    }

    /**
     * @return array<int,LocatedTextEdits>
     * @param LocatedTextEdit[] $edits
     */
    public static function fromLocatedEditsToCollection(array $edits): array
    {
        $byPath = [];
        $locatedEdits = [];
        foreach ($edits as $edit) {
            if (!isset($byPath[$edit->documentUri()->__toString()])) {
                $byPath[$edit->documentUri()->__toString()] = [];
            }
            $byPath[$edit->documentUri()->__toString()][] = $edit->textEdit();
        }
        foreach ($byPath as $path => $edits) {
            $locatedEdits[] = new self(TextEdits::fromTextEdits($edits), TextDocumentUri::fromString($path));
        }

        return $locatedEdits;
    }
}
