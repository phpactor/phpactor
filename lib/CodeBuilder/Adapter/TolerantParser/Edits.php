<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Token;
use Phpactor\CodeBuilder\Util\TextFormat;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;

class Edits
{
    /**
     * @var array<TextEdit>
     */
    private array $edits = [];

    private TextFormat $format;

    public function __construct(?TextFormat $format = null)
    {
        $this->format = $format ?: new TextFormat();
    }

    /**
     * @param Node|Token $node
     */
    public function remove($node): void
    {
        $this->edits[] = TextEdit::create($node->getFullStartPosition(), $node->getFullWidth(), '');
    }

    /**
     * @param Node|Token $node
     */
    public function before($node, string $text): void
    {
        $this->edits[] = TextEdit::create($node->getStartPosition(), 0, $text);
    }

    /**
     * @param Node|Token $node
     */
    public function after($node, string $text): void
    {
        $this->edits[] = TextEdit::create($node->getEndPosition(), 0, $text);
    }

    /**
     * @param Node|Token|QualifiedName $node
     */
    public function replace($node, string $text): void
    {
        $this->edits[] = TextEdit::create($node->getFullStartPosition(), $node->getFullWidth(), $text);
    }

    public function replaceMultiple(
        Node|Token|QualifiedName $firstNodeToReplace,
        Node|Token|QualifiedName $lastToReplace,
        string $text,
    ): void {
        $this->edits[] = TextEdit::create(
            $firstNodeToReplace->getStartPosition(),
            $lastToReplace->getEndPosition() - $firstNodeToReplace->getStartPosition(),
            $text
        );
    }

    public function textEdits(): TextEdits
    {
        return TextEdits::fromTextEdits($this->edits);
    }

    public function add(TextEdit $textEdit): void
    {
        $this->edits[] = $textEdit;
    }

    public function indent(string $string, int $level): string
    {
        return $this->format->indent($string, $level);
    }
}
