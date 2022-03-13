<?php

namespace Phpactor\Completion\Core\Completor;

use Generator;
use Phpactor\Completion\Core\Completor;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class DedupeCompletor implements Completor
{
    private Completor $innerCompletor;

    private bool $matchShortDescription;

    public function __construct(Completor $innerCompletor, bool $matchShortDescription = false)
    {
        $this->innerCompletor = $innerCompletor;
        $this->matchShortDescription = $matchShortDescription;
    }

    
    public function complete(TextDocument $source, ByteOffset $byteOffset): Generator
    {
        $seen = [];
        $suggestions = $this->innerCompletor->complete($source, $byteOffset);
        foreach ($suggestions as $suggestion) {
            $key = $suggestion->name();

            if ($this->matchShortDescription) {
                $key .= $suggestion->shortDescription();
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
