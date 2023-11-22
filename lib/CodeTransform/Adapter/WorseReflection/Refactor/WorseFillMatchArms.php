<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Refactor;

use Microsoft\PhpParser\MissingToken;
use Microsoft\PhpParser\Node\Expression\MatchExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\MatchArm;
use Microsoft\PhpParser\Parser;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionMatchExpression;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnum;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\Reflector;

final class WorseFillMatchArms
{
    public function __construct(
        private Reflector $reflector,
        private Parser $parser,
    ) {
    }

    public function fillMatchArms(TextDocument $document, ByteOffset $offset): TextEdits
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
        [$prefix, $postfix, $start, $existingCases] = $this->existingCases($node);
        if ($prefix) {
            $edits[] = TextEdit::create($start, 0, $prefix);
        }
        foreach ($enum->cases() as $case) {
            if (in_array($case->name(), $existingCases)) {
                continue;
            }
            $edits[] = TextEdit::create($start, 0, sprintf('%s::%s => null,', $enum->name()->short(), $case->name()));
        }
        if ($postfix) {
            $edits[] = TextEdit::create($start, 0, $postfix);
        }

        return TextEdits::fromTextEdits($edits);
    }

    /**
     * @return array{?string,?string,int,string[]}
     */
    private function existingCases(MatchExpression $node): array
    {
        $start = $node->openBrace->getStartPosition() + 1;
        $prefix = null;
        $postfix = null;
        if ($node->openBrace instanceof MissingToken) {
            $prefix = '{';
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
        return [$prefix, $postfix, $start, $cases];
    }
}
