<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Refactor;

use Phpactor\CodeTransform\Domain\Refactor\ExtractConstant;
use Phpactor\TextDocument\TextDocumentEdits;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Reflector;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\ClassLike;
use Microsoft\PhpParser\Node\StringLiteral;
use Microsoft\PhpParser\Node\NumericLiteral;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Inference\SymbolContext;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Domain\Exception\TransformException;
use Phpactor\WorseReflection\TypeUtil;

class WorseExtractConstant implements ExtractConstant
{
    private Reflector $reflector;

    private Updater $updater;

    private Parser $parser;

    public function __construct(Reflector $reflector, Updater $updater, Parser $parser = null)
    {
        $this->reflector = $reflector;
        $this->updater = $updater;
        $this->parser = $parser ?: new Parser();
    }

    public function extractConstant(SourceCode $sourceCode, int $offset, string $constantName): TextDocumentEdits
    {
        $symbolInformation = $this->reflector
            ->reflectOffset($sourceCode->__toString(), $offset)
            ->symbolContext();

        $textEdits = $this->addConstant($sourceCode, $symbolInformation, $constantName);
        $textEdits = $textEdits->merge($this->replaceValues($sourceCode, $offset, $constantName));
        return new TextDocumentEdits(TextDocumentUri::fromString($sourceCode->path()), $textEdits);
    }

    public function canExtractConstant(SourceCode $source, int $offset): bool
    {
        $node = $this->parser->parseSourceFile($source->__toString());
        $targetNode = $node->getDescendantNodeAtPosition($offset);
        try {
            $this->getComparableValue($targetNode);
        } catch (TransformException $e) {
            return false;
        }
        return true;
    }

    private function addConstant(string $sourceCode, SymbolContext $symbolInformation, string $constantName): TextEdits
    {
        $symbol = $symbolInformation->symbol();

        $builder = SourceCodeBuilder::create();
        $containerType = $symbolInformation->containerType();

        if (!$containerType) {
            throw new TransformException(sprintf('Node does not belong to a class'));
        }

        $containerType = TypeUtil::unwrapNullableType($containerType);
        if (!$containerType instanceof ClassType) {
            throw new TransformException(sprintf('Could not find container class'));
        }

        $builder->namespace($containerType->name()->namespace());
        $builder
            ->class($containerType->name()->short())
                ->constant($constantName, $symbolInformation->value())
            ->end();

        return $this->updater->textEditsFor($builder->build(), Code::fromString($sourceCode));
    }

    private function replaceValues(SourceCode $sourceCode, int $offset, string $constantName): TextEdits
    {
        $node = $this->parser->parseSourceFile($sourceCode->__toString());
        $targetNode = $node->getDescendantNodeAtPosition($offset);
        $targetValue = $this->getComparableValue($targetNode);
        $classNode = $targetNode->getFirstAncestor(ClassLike::class);

        if (null === $classNode) {
            throw new TransformException('Node does not belong to a class');
        }

        $textEdits = [];
        foreach ($classNode->getDescendantNodes() as $node) {
            if (!$node instanceof $targetNode) {
                continue;
            }

            if ($targetValue == $this->getComparableValue($node)) {
                $textEdits[] = TextEdit::create(
                    $node->getStartPosition(),
                    $node->getEndPosition() - $node->getStartPosition(),
                    'self::' . $constantName
                );
            }
        }

        return TextEdits::fromTextEdits($textEdits);
    }

    private function getComparableValue(Node $node): string
    {
        if ($node instanceof StringLiteral) {
            return $node->getStringContentsText();
        }

        if ($node instanceof NumericLiteral) {
            return $node->getText();
        }

        throw new TransformException(sprintf(
            'Do not know how to replace node of type "%s"',
            get_class($node)
        ));
    }
}
