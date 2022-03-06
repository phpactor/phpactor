<?php

namespace Phpactor\Filesystem\Iterator;

use ArrayIterator;
use Iterator;

/**
 * Custom implementation of AppendIterator as a work-around for PHP bug in 7.1.0 / 7.2/BETA:
 *
 * https://bugs.php.net/bug.php?id=75155&edit=2
 * https://3v4l.org/Hnti2
 */
class AppendIterator implements Iterator
{
    private $iterators = [];
    private $index = 0;

    /**
     * Appends an iterator
     * @link http://php.net/manual/en/appenditerator.append.php
     * @param Iterator $iterator <p>
     * The iterator to append.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function append(Iterator $iterator): void
    {
        $this->iterators[] = $iterator;
    }

    /**
     * Rewinds the Iterator
     * @link http://php.net/manual/en/appenditerator.rewind.php
     * @return void
     * @since 5.1.0
     */
    public function rewind(): void
    {
        $this->index = 0;
        foreach ($this->iterators as $iterator) {
            $iterator->rewind();
        }
    }

    /**
     * Checks validity of the current element
     * @link http://php.net/manual/en/appenditerator.valid.php
     * @return bool true on success or false on failure.
     * @since 5.1.0
     */
    public function valid()
    {
        if (false === isset($this->iterators[$this->index])) {
            return false;
        }

        return $this->iterators[$this->index]->valid();
    }

    /**
     * Gets the current key
     * @link http://php.net/manual/en/appenditerator.key.php
     * @return mixed The current key if it is valid or null otherwise.
     * @since 5.1.0
     */
    public function key()
    {
        return $this->iterators[$this->index]->key();
    }

    /**
     * Gets the current value
     * @link http://php.net/manual/en/appenditerator.current.php
     * @return mixed The current value if it is valid or &null; otherwise.
     * @since 5.1.0
     */
    public function current()
    {
        return $this->iterators[$this->index]->current();
    }

    /**
     * Moves to the next element
     * @link http://php.net/manual/en/appenditerator.next.php
     * @return void
     * @since 5.1.0
     */
    public function next(): void
    {
        $iterator = $this->iterators[$this->index];
        $next = $iterator->next();

        if (false === $this->valid() && isset($this->iterators[$this->index + 1])) {
            $this->index++;
        }
    }

    /**
     * Gets an inner iterator
     * @link http://php.net/manual/en/appenditerator.getinneriterator.php
     * @return Iterator the current inner Iterator.
     * @since 5.1.0
     */
    public function getInnerIterator()
    {
        return $this->iterators[$this->index];
    }

    /**
     * Gets an index of iterators
     * @link http://php.net/manual/en/appenditerator.getiteratorindex.php
     * @return int The index of iterators.
     * @since 5.1.0
     */
    public function getIteratorIndex()
    {
        return $this->index;
    }

    /**
     * The getArrayIterator method
     * @link http://php.net/manual/en/appenditerator.getarrayiterator.php
     * @return ArrayIterator containing the appended iterators.
     * @since 5.1.0
     */
    public function getArrayIterator()
    {
    }
}
