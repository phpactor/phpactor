<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Refactor;

use Phpactor\WorseReflection\Core\AstProvider;
use Microsoft\PhpParser\Node\Attribute;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeTransform\Adapter\WorseReflection\Helper\EmptyValueRenderer;
use Phpactor\CodeTransform\Domain\Refactor\ByteOffsetRefactor;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflection\ReflectionObjectCreationExpression;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\HasEmptyType;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Reflector;

class WorseFillObject implements ByteOffsetRefactor
{
    private EmptyValueRenderer $valueRenderer;

    public function __construct(
        private Reflector $reflector,
        private AstProvider $parser,
        private Updater $updater,
        private bool $namedParameters = true,
        private bool $hint = true
    ) {
        $this->valueRenderer = new EmptyValueRenderer();
    }

    public function refactor(TextDocument $document, ByteOffset $offset): TextEdits
    {
        /** @var ObjectCreationExpression|Attribute|null $node */
        $node = $this->parser
            ->parseSourceFile($document->__toString())
            ->getDescendantNodeAtPosition($offset->toInt())
            ->getFirstAncestor(ObjectCreationExpression::class, Attribute::class)
        ;
        if ($node === null) {
            return TextEdits::none();
        }

        try {
            $offset = $this->reflector->reflectNode($document, $node->getStartPosition());
        } catch (NotFound $notFound) {
            return TextEdits::none();
        }

        if (!$offset instanceof ReflectionObjectCreationExpression) {
            return TextEdits::none();
        }

        // do not support existing arguments
        if ($offset->arguments()->count() !== 0) {
            return TextEdits::none();
        }

        try {
            $constructor = $offset->class()->methods()->get('__construct');
        } catch (NotFound) {
            return TextEdits::none();
        }

        $args = [];

        $imports = [];
        foreach ($constructor->parameters() as $parameter) {
            assert($parameter instanceof ReflectionParameter);
            $parameterType = $parameter->type();
            if ($parameterType instanceof ReflectedClassType && $parameterType->isInterface()->isFalse()) {
                $imports[] = $parameterType;
            }
            $arg = [];
            if ($this->namedParameters) {
                $arg[] = sprintf(
                    '%s: ',
                    $parameter->name(),
                );
            }
            $arg[] = $this->renderEmptyValue($parameterType);

            if ($this->hint) {
                $arg[] = sprintf(' /** $%s %s */', $parameter->name(), $parameter->type()->__toString());
            }
            $args[] = implode('', $arg);
        }

        $sourceCode = SourceCodeBuilder::create();
        foreach ($imports as $import) {
            $sourceCode->use($import->__toString());
        }
        $textEdits = $this->updater->textEditsFor($sourceCode->build(), Code::fromString($document->__toString()));

        $openParen = $closedParen = '';
        if ($node->openParen) {
            $endPosition = $node->openParen->getEndPosition();
        } else {
            $endPosition = $node->getEndPosition();
            $openParen = '(';
        }
        if (!$node->closeParen) {
            $closedParen = ')';
        }

        return $textEdits->add(TextEdit::create(
            $endPosition,
            0,
            sprintf('%s%s%s', $openParen, implode(', ', $args), $closedParen),
        ));
    }

    private function renderEmptyValue(Type $type): string
    {
        if (!$type instanceof HasEmptyType) {
            return sprintf('/** %s */', $type->__toString());
        }

        return $this->valueRenderer->render($type->emptyType());
    }
}
