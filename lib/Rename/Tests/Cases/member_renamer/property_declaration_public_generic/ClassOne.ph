<?php

/**
 * @template T
 */
class ClassOne
{
    /**
     * @param T
     */
    public string $foobar;

    /**
     * @param T $foobar
     */
    public function __construct(string $foobar)
    {
        $this->foobar = $foobar;
    }
}
