<?php

namespace Phpactor\Extension\LanguageServerReferenceFinder\Model;

use ArrayIterator;
use Countable;
use Iterator;
use IteratorAggregate;
use Phpactor\LanguageServerProtocol\DocumentHighlight;
use RuntimeException;

/**
 * @implements IteratorAggregate<DocumentHighlight>
 */
class Highlights implements IteratorAggregate, Countable
{
    /**
     * @var array<DocumentHighlight>
     */
    private array $highlights;

    public function __construct(DocumentHighlight ...$highlights)
    {
        $this->highlights = $highlights;
    }

    public function first(): DocumentHighlight
    {
        if ($this->highlights === []) {
            throw new RuntimeException('Document highlights are empty');
        }

        return $this->highlights[0];
    }

    public function at(int $index): DocumentHighlight
    {
        if (!isset($this->highlights[$index])) {
            throw new RuntimeException(sprintf(
                'No highlight at offset "%s"',
                $index
            ));
        }

        return $this->highlights[$index];
    }

    /**
     * @return ArrayIterator<int,DocumentHighlight>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->highlights);
    }

    public static function fromIterator(Iterator $iterator): self
    {
        return new self(...iterator_to_array($iterator));
    }


    public function count(): int
    {
        return count($this->highlights);
    }

    /**
     * @return array<DocumentHighlight>
     */
    public function toArray(): array
    {
        return $this->highlights;
    }

    public static function empty(): self
    {
        return new self();
    }
}
