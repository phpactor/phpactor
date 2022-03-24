<?php

namespace Phpactor\Extension\LanguageServerRename\Model;

use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\TextEdits;

final class LocatedTextEditsMap
{
    /**
     * @var array<string,TextEdits>
     */
    private array $editMap = [];

    /**
     * @var array<string,TextDocumentUri>
     */
    private array $moveMap = [];

    public function __construct(array $editMap, array $moveMap)
    {
        $this->editMap = $editMap;
        $this->moveMap = $moveMap;
    }

    public static function create(): self
    {
        return new self([], []);
    }

    /**
     * @param LocatedTextEdit[] $locatedEdits
     */
    public static function fromLocatedEdits(array $locatedEdits): self
    {
        $map = new self([], []);
        foreach ($locatedEdits as $locationEdit) {
            $map = $map->withTextEdit($locationEdit);
        }

        return $map;
    }

    public function withTextEdit(LocatedTextEdit $edit): self
    {
        $editMap = $this->editMap;
        $moveMap = $this->moveMap;
        $uri = $edit->documentUri();

        if (!isset($editMap[$uri->__toString()])) {
            $editMap[$uri->__toString()] = new TextEdits();
        }

        $editMap[$uri->__toString()] = $editMap[$uri->__toString()]->add($edit->textEdit());

        if (null !== $edit->newDocumentUri()) {
            $moveMap[$uri->__toString()] = $edit->newDocumentUri();
        }

        return new self($editMap, $moveMap);
    }

    public function merge(self $map): self
    {
        $me = $this;

        foreach ($map->toLocatedTextEdits() as $textEdit) {
            foreach ($textEdit->textEdits() as $edit) {
                $me = $me->withTextEdit(new LocatedTextEdit($textEdit->documentUri(), $edit, $textEdit->newDocumentUri()));
            }
        }

        return $me;
    }

    /**
     * @return LocatedTextEdits[]
     */
    public function toLocatedTextEdits(): array
    {
        $locatedTextEdits = [];
        foreach ($this->editMap as $uri => $edits) {
            $locatedTextEdits[] = new LocatedTextEdits(
                $edits,
                TextDocumentUri::fromString($uri),
                $this->moveMap[$uri] ?? null,
            );
        }

        return $locatedTextEdits;
    }
}
