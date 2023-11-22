<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Refactor;

use Microsoft\PhpParser\Node\Expression\MatchExpression;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeTransform\Adapter\WorseReflection\Helper\EmptyValueRenderer;
use Phpactor\CodeTransform\Domain\Refactor\FillObject;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionMatchExpression;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnum;
use Phpactor\WorseReflection\Core\Reflection\ReflectionObjectCreationExpression;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\HasEmptyType;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Reflector;

class WorseFillMatchArms implements FillObject
{
    public function __construct(
        private Reflector $reflector,
        private Parser $parser,
        private Updater $updater,
        private bool $namedParameters = true,
        private bool $hint = true
    ) {
    }

    public function fillObject(TextDocument $document, ByteOffset $offset): TextEdits
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
        $start = $node->openBrace->getStartPosition() + 1;
        foreach ($enum->cases() as $case) {
            $edits[] = TextEdit::create($start, 0, sprintf('%s::%s => null,', $enum->name()->short(), $case->name()));
        }

        return TextEdits::fromTextEdits($edits);
    }

    private function renderEmptyValue(Type $type): string
    {
        if (!$type instanceof HasEmptyType) {
            return sprintf('/** %s */', $type->__toString());
        }

        return $this->valueRenderer->render($type->emptyType());
    }
}
