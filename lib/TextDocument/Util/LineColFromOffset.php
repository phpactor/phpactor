<?php

namespace Phpactor\TextDocument\Util;

use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\LineCol;

class LineColFromOffset
{
    /**
     * @deprecated Use LineCol Value object
     */
    public function __invoke(string $document, int $byteOffset): LineCol
    {
        return LineCol::fromByteOffset($document, ByteOffset::fromInt($byteOffset));
    }
}
