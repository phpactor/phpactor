<?php

namespace Phpactor\Completion\Bridge\TolerantParser;

use Generator;
use Microsoft\PhpParser\Node;
use Phpactor\Completion\Bridge\TolerantParser\Qualifier\AlwaysQualfifier;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class LimitingCompletor implements TolerantCompletor, TolerantQualifiable
{
    /**
     * @var TolerantCompletor|TolerantQualifiable
     */
    private $completor;

    private int $limit;

    public function __construct(TolerantCompletor $completor, int $limit = 50)
    {
        $this->completor = $completor;
        $this->limit = $limit;
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
}
