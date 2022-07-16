<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Refactor;

use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeTransform\Adapter\WorseReflection\Helper\EmptyValueRenderer;
use Phpactor\CodeTransform\Domain\Refactor\FillObject;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Core\Reflection\ReflectionObjectCreationExpression;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\HasEmptyType;
use Phpactor\WorseReflection\Reflector;

class WorseFillObject implements FillObject
{
    private Reflector $reflector;

    private Parser $parser;

    private EmptyValueRenderer $valueRenderer;

    public function __construct(Reflector $reflector, Parser $parser)
    {
        $this->reflector = $reflector;
        $this->parser = $parser;
        $this->valueRenderer = new EmptyValueRenderer();
    }

    public function fillObject(TextDocument $document, ByteOffset $offset): TextEdits
    {
        $node = $this->parser->parseSourceFile($document->__toString())->getDescendantNodeAtPosition($offset->toInt());
        $node = $node->getFirstAncestor(ObjectCreationExpression::class);
        if (!$node instanceof ObjectCreationExpression) {
            return TextEdits::none();
        }
        $offset = $this->reflector->reflectNode($document, $node->getStartPosition());

        if (!$offset instanceof ReflectionObjectCreationExpression) {
            return TextEdits::none();
        }

        // do not support existing arguments
        if ($offset->arguments()->count() !== 0) {
            return TextEdits::none();
        }

        $constructor = $offset->class()->methods()->get('__construct');

        $args = [];

        foreach ($constructor->parameters() as $parameter) {
            assert($parameter instanceof ReflectionParameter);
            $args[] = sprintf('%s: %s', $parameter->name(), $this->renderEmptyValue($parameter->type()));
        }


        return TextEdits::one(TextEdit::create($node->openParen->getEndPosition(), 0, implode(', ', $args)));
    }

    private function renderEmptyValue(Type $type): string
    {
        if (!$type instanceof HasEmptyType) {
            return sprintf('/** %s */', $type->__toString());
        }

        return $this->valueRenderer->render($type->emptyType());
    }
}
