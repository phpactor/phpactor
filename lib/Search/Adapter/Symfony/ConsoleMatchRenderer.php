<?php

namespace Phpactor\Search\Adapter\Symfony;

use Phpactor\Search\Model\MatchRenderer;
use Phpactor\Search\Model\PatternMatch;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\Util\LineAtOffset;

final class ConsoleMatchRenderer implements MatchRenderer
{
    public function render(TextDocument $document, PatternMatch $match): string
    {
        foreach ($match->tokens() as $token) {
            dump($token);
        }
    }
}
