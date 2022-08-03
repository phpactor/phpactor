<?php

namespace Phpactor\Completion\Core\Completor;

use Generator;
use Phpactor\Completion\Core\Completor;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class DedupeCompletor implements Completor
{
    private Completor $innerCompletor;

    private bool $matchNameImport;

    public function __construct(Completor $innerCompletor, bool $matchNameImport = false)
    {
        $this->innerCompletor = $innerCompletor;
        $this->matchNameImport = $matchNameImport;
    }


    public function complete(TextDocument $source, ByteOffset $byteOffset): Generator
    {
        $seen = [];
        $suggestions = $this->innerCompletor->complete($source, $byteOffset);
        foreach ($suggestions as $suggestion) {
            $key = $suggestion->name();

            if ($this->matchNameImport) {
                $key .= $suggestion->nameImport();
            }

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = $suggestion;

            yield $suggestion;
        }

        return $suggestions->getReturn();
    }
}
