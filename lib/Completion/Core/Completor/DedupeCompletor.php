<?php

namespace Phpactor\Completion\Core\Completor;

use Generator;
use Phpactor\Completion\Core\Completor;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class DedupeCompletor implements Completor
{
    public function __construct(private Completor $innerCompletor, private bool $matchNameImport = false)
    {
    }


    public function complete(TextDocument $source, ByteOffset $byteOffset): Generator
    {
        $seen = [];
        $suggestions = $this->innerCompletor->complete($source, $byteOffset);
        foreach ($suggestions as $suggestion) {
            $key = $suggestion->name().$suggestion->type();

            if ($this->matchNameImport) {
                $key .= $suggestion->fqn();
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
