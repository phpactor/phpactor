<?php
declare(strict_types=1);

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Refactor;

use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Parameter;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\Prototype\Visibility;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeTransform\Domain\Refactor\PromoteProperty;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Reflector;

class WorsePromoteProperty implements PromoteProperty
{
    public function __construct(
        private Reflector $reflector,
        private Parser $parser,
        private Updater $updater,
    ) {
    }

    public function promoteProperty(TextDocument $document, ByteOffset $offset): TextEdits
    {
        $node = $this->parser->parseSourceFile($document->__toString())->getDescendantNodeAtPosition($offset->toInt());
        $methodNode = $node->getFirstAncestor(MethodDeclaration::class);

        if (!$methodNode instanceof MethodDeclaration) {
            return TextEdits::none();
        }

        if ($methodNode->getName() !== '__construct') {
            return TextEdits::none();
        }

        $parameter = $node->getFirstAncestor(Parameter::class);
        if (!$parameter instanceof Parameter) {
            return TextEdits::none();
        }
        $parameterName = $parameter->getName();

        $classNode = $node->getFirstAncestor(ClassDeclaration::class);
        // You can't have method declarations outside of classes
        assert($classNode instanceof ClassDeclaration);

        $className = $classNode->name->getText((string) $document);

        $sourceCode = SourceCodeBuilder::create();
        $methodBuilder = $sourceCode->class($className)->method('__construct');

        $methodBuilder->parameter($parameterName)->visibility(Visibility::private());

        return $this->updater->textEditsFor($sourceCode->build(), Code::fromString($document->__toString()));
    }
}
