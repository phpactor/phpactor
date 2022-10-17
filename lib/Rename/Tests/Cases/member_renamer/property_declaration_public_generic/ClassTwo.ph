<?php

/**
 * @template T
 */
class ClassTwo extends ClassOne
{
    public function barfoo(): string
    {
        return $this->foobar;
    }
}
