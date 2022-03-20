<?php

namespace Phpactor\WorseReflection\DocblockParser\Ast;

interface Element
{
    /**
     * Return the string aggregation of all tokens in this element
     */
    public function toString(): string;

    /**
     * Return the start byte offset, starting at 0
     */
    public function start(): int;

    /**
     * Return the end byte offset, starting at 0
     */
    public function end(): int;
}
