<?php

namespace Phpactor\Completion\Core\Completor;

use Generator;
use Phpactor\Completion\Core\Completor;
use Phpactor\Completion\Core\LabelFormatter;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class LabelFormattingCompletor implements Completor
{
    public function __construct(
        private readonly Completor $completor,
        private readonly LabelFormatter $labelFormatter
    ) {
    }

    public function complete(
        TextDocument $source,
        ByteOffset $byteOffset
    ): Generator {
        $seen = [];
        $suggestions = $this->completor->complete($source, $byteOffset);
        foreach ($suggestions as $suggestion) {
            if (
                $suggestion->type() === Suggestion::TYPE_CLASS ||
                $suggestion->type() === Suggestion::TYPE_CONSTANT ||
                $suggestion->type() === Suggestion::TYPE_FUNCTION
            ) {
                $label = $this->labelFormatter->format($suggestion->fqn() ?? $suggestion->label(), $seen);
                $seen[$label] = true;
                yield $suggestion->withLabel($label);
                continue;
            }


            yield $suggestion;
        }

        return $suggestions->getReturn();
    }
}
