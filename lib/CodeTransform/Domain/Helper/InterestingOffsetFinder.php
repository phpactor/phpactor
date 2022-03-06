<?php

namespace Phpactor\CodeTransform\Domain\Helper;

use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

/**
 * Return the nearest offset, relative to the given offset position, that is
 * interesting.
 *
 * For example, if we are on:
 *
 * - whitespace within a class, return offset of class.
 * - whitespace within a method, return offset of method
 * - if the given offset is already interesting, then return that.
 *
 * This utility can be used to find an object of interest relative to a given
 * offset, useful for context menus.
 */
interface InterestingOffsetFinder
{
    public function find(TextDocument $source, ByteOffset $offset): ByteOffset;
}
