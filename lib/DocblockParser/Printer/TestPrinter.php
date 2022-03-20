<?php

namespace Phpactor\DocblockParser\Printer;

use Phpactor\DocblockParser\Ast\Element;
use Phpactor\DocblockParser\Ast\Node;
use Phpactor\DocblockParser\Printer;
use Phpactor\DocblockParser\Ast\Token;

final class TestPrinter implements Printer
{
    private int $indent = 0;

    public function print(Node $node): string
    {
        $this->indent++;
        $out = sprintf('%s: = ', $node->shortName());
        foreach ($node->children() as $child) {
            $out .= $this->printElement($child);
        }
        $this->indent--;

        return $out;
    }

    /**
     * @param Element|Element[] $element
     */
    public function printElement($element): string
    {
        if ($element instanceof Token) {
            return sprintf('%s', $element->value);
        }

        if ($element instanceof Node) {
            return $this->newLine() . $this->print($element);
        }

        return implode('', array_map(function (Element $element) {
            return $this->printElement($element);
        }, (array)$element));
    }

    private function newLine(): string
    {
        return "\n".str_repeat(' ', $this->indent);
    }
}
