<?php

namespace Phpactor\Completion\Bridge\TolerantParser;

use Generator;
use Microsoft\PhpParser\Node;
use Phpactor\Completion\Bridge\TolerantParser\Qualifier\AlwaysQualfifier;
use Phpactor\Completion\Core\CompletorDecorator;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class LimitingCompletor implements TolerantCompletor, TolerantQualifiable, CompletorDecorator
{
    public function __construct(
        private TolerantCompletor $completor,
        private int $limit = 50
    ) {
    }


    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        /** @var TolerantCompletor $completor */
        $completor = $this->completor;
        $count = 0;
        $suggestions = $completor->complete($node, $source, $offset);
        foreach ($suggestions as $suggestion) {
            yield $suggestion;

            if (++$count === $this->limit) {
                return false;
            }
        }

        return $suggestions->getReturn();
    }

    public function qualifier(): TolerantQualifier
    {
        if (!$this->completor instanceof TolerantQualifiable) {
            return new AlwaysQualfifier();
        }

        return $this->completor->qualifier();
    }

    public function decorates(): object
    {
        return $this->completor;
    }
}
