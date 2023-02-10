<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Refactor;

use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Parameter;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\Prototype\Visibility;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeTransform\Domain\Refactor\PromoteProperty;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\SourceCode;
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

        if(count($methodNode->parameters->children) === 0) {
            return TextEdits::none();
        }

        $parameterNode = $node->getFirstAncestor(Parameter::class);
        if (!$parameterNode instanceof Parameter::class) {
            return TextEdits::none();
        }

        $parameter = $this->reflector->reflectNode($parameterNode, $methodNode->getStartPosition());
        if (!$parameter instanceof ReflectionParameter) {
            return TextEdits::none();
        }

        // Todo stuff
    }
}
