<?php

namespace Phpactor\Extension\LanguageServerReferenceFinder\Model;

use Amp\Promise;
use Phpactor\TextDocument\ByteOffset;

interface Highlighter
{
    /**
     * @return Promise<Highlights>
     */
    public function highlightsFor(string $source, ByteOffset $offset): Promise;
}
