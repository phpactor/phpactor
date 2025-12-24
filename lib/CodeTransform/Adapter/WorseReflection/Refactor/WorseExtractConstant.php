<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Refactor;

use Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider;
use Phpactor\CodeTransform\Domain\Refactor\ExtractConstant;
use Phpactor\TextDocument\TextDocumentEdits;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Reflector;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\WorseReflection\Core\AstProvider;
use Microsoft\PhpParser\ClassLike;
use Microsoft\PhpParser\Node\StringLiteral;
use Microsoft\PhpParser\Node\NumericLiteral;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Domain\Exception\TransformException;
use Phpactor\WorseReflection\TypeUtil;

class WorseExtractConstant implements ExtractConstant
{
    public function __construct(
        private Reflector $reflector,
        private Updater $updater,
        private AstProvider $parser = new TolerantAstProvider(),
    ) {
    }

    public function extractConstant(SourceCode $sourceCode, int $offset, string $constantName): TextDocumentEdits
    {
        $symbolInformation = $this->reflector
            ->reflectOffset($sourceCode, $offset)
            ->nodeContext();

        $textEdits = $this->addConstant($sourceCode, $symbolInformation, $constantName);
        $textEdits = $textEdits->merge($this->replaceValues($sourceCode, $offset, $constantName));
        return new TextDocumentEdits(TextDocumentUri::fromString($sourceCode->uri()->path()), $textEdits);
    }

    public function canExtractConstant(SourceCode $source, int $offset): bool
    {
        $node = $this->parser->get($source->__toString());
        $targetNode = $node->getDescendantNodeAtPosition($offset);
        try {
            $this->getComparableValue($targetNode);
        } catch (TransformException) {
            return false;
        }
        return true;
    }

    private function addConstant(string $sourceCode, NodeContext $symbolInformation, string $constantName): TextEdits
    {
        $symbol = $symbolInformation->symbol();

        $builder = SourceCodeBuilder::create();
        $classType = $symbolInformation->containerType()->expandTypes()->classLike()->firstOrNull();

        if (!$classType) {
            throw new TransformException('Node does not belong to a class');
        }

        if ($classType->members()->constants()->has($constantName)) {
            throw new TransformException(
                sprintf(
                    'Constant with name %s already exists on class %s',
                    $constantName,
                    $classType->name()->short()
                )
            );
        }

        $builder->namespace($classType->name()->namespace());
        $builder
            ->class($classType->name()->short())
                ->constant($constantName, TypeUtil::valueOrNull($symbolInformation->type()))
            ->end();

        return $this->updater->textEditsFor($builder->build(), Code::fromString($sourceCode));
    }

    private function replaceValues(SourceCode $sourceCode, int $offset, string $constantName): TextEdits
    {
        $node = $this->parser->get($sourceCode->__toString());
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
