<?php

namespace Phpactor\Completion\Core\Completor;

use Generator;
use Phpactor\Completion\Core\Completor;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use RuntimeException;

class LimitingCompletor implements Completor
{
    /**
     * @var Completor
     */
    private $innerCompletor;

    /**
     * @var int
     */
    private $limit;

    public function __construct(Completor $innerCompletor, int $limit = 32)
    {
        $this->innerCompletor = $innerCompletor;
        $this->limit = $limit;

        if ($limit < 0) {
            throw new RuntimeException(sprintf(
                'Limit cannot be less than 0, got %d',
                $limit
            ));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function complete(TextDocument $source, ByteOffset $byteOffset): Generator
    {
        $count = 0;
        $suggestions = $this->innerCompletor->complete($source, $byteOffset);
        foreach ($suggestions as $suggestion) {
            if ($count++ >= $this->limit) {
                return false;
            }
            yield $suggestion;
        }

        return $suggestions->getReturn();
    }
}
