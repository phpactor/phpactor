<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Refactor;

use Microsoft\PhpParser\MissingToken;
use Microsoft\PhpParser\Node\Expression\MatchExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\MatchArm;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeTransform\Domain\Refactor\ByteOffsetRefactor;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Phpactor\TextDocument\Util\LineAtOffset;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionMatchExpression;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnum;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\Reflector;

final class WorseFillMatchArms implements ByteOffsetRefactor
{
    public function __construct(
        private Reflector $reflector,
        private Parser $parser,
    ) {
    }

    public function refactor(TextDocument $document, ByteOffset $offset): TextEdits
    {
        $node = $this->parser->parseSourceFile($document->__toString())->getDescendantNodeAtPosition($offset->toInt());
        $node = $node instanceof MatchExpression ? $node : $node->getFirstAncestor(MatchExpression::class);
        if (!$node instanceof MatchExpression) {
            return TextEdits::none();
        }
        try {
            $reflectionNode = $this->reflector->reflectNode($document, $node->getStartPosition());
        } catch (NotFound $notFound) {
            return TextEdits::none();
        }

        if (!$reflectionNode instanceof ReflectionMatchExpression) {
            return TextEdits::none();
        }

        $type = $reflectionNode->expressionType();
        if (!$type instanceof ReflectedClassType) {
            return TextEdits::none();
        }

        $enum = $type->reflectionOrNull();
        if (!$enum instanceof ReflectionEnum) {
            return TextEdits::none();
        }

        $edits = [];
        [$prefix, $whitespace, $postfix, $start, $existingCases] = $this->existingCases($node);
        if ($prefix) {
            $edits[] = TextEdit::create($start, 0, $prefix);
        }
        $edits[] = TextEdit::create($start, 0, "\n");
        foreach ($enum->cases() as $case) {
            if (in_array($case->name(), $existingCases)) {
                continue;
            }
            $edits[] = TextEdit::create($start, 0, sprintf("%s%s::%s => null,\n", $whitespace, $enum->name()->short(), $case->name()));
        }
        $edits[] = TextEdit::create($start, 0, $whitespace);
        if ($postfix) {
            $edits[] = TextEdit::create($start, 0, $postfix);
        }

        return TextEdits::fromTextEdits($edits);
    }

    /**
     * @return array{?string,string,?string,int,string[]}
     */
    private function existingCases(MatchExpression $node): array
    {
        $start = $node->openBrace->getStartPosition() + 1;
        $prefix = null;
        $postfix = null;
        $whitespace = '';
        if ($node->openBrace instanceof MissingToken) {
            $prefix = "{\n";
        }
        if ($node->closeBrace instanceof MissingToken) {
            $postfix = '}';
        }
        $cases = [];
        foreach ($node->arms?->getChildNodes() ?? [] as $arm) {
            assert($arm instanceof MatchArm);
            $start = $arm->getEndPosition() + 1;
            foreach ($arm->conditionList->getChildNodes() as $node) {
                if (!$node instanceof ScopedPropertyAccessExpression) {
                    continue;
                }
                $cases[] = NodeUtil::nameFromTokenOrNode($node, $node->memberName);

            }
        }
        $line = LineAtOffset::lineAtByteOffset($node->getFileContents(), ByteOffset::fromInt($start));
        if (preg_match('{^\s+}', $line, $matches)) {
            $whitespace = $matches[0];
        }
        return [$prefix, $whitespace, $postfix, $start, $cases];
    }
}
