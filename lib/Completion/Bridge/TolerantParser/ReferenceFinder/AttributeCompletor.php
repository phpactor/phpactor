<?php

namespace Phpactor\Completion\Bridge\TolerantParser\ReferenceFinder;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Parameter;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\FunctionDeclaration;
use Microsoft\PhpParser\TokenKind;
use Phpactor\Completion\Bridge\TolerantParser\CompletionContext;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\Completor\NameSearcherCompletor;
use Phpactor\Name\NameUtil;
use Phpactor\ReferenceFinder\NameSearcherType;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class AttributeCompletor extends NameSearcherCompletor implements TolerantCompletor
{
    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        if (!CompletionContext::attribute($node)) {
            return true;
        }

        $name = $node->__toString();
        if ($node instanceof QualifiedName && NameUtil::isQualified($name)) {
            $name = NameUtil::toFullyQualified((string)$node->getResolvedName());
        }

        $targetNode = $node->getFirstAncestor(
            ClassDeclaration::class,
            FunctionDeclaration::class,
            MethodDeclaration::class,
            PropertyDeclaration::class,
            ClassConstDeclaration::class,
            Parameter::class,
        );

        yield from $this->completeName($name, $source->uri(), $node, $this->matchTargetNode($targetNode));

        return true;
    }

    /**
     * @return NameSearcherType::ATTRIBUTE_TARGET_*|NameSearcherType::ATTRIBUTE
     */
    private function matchTargetNode(?Node $targetNode): string
    {
        if (null === $targetNode) {
            return NameSearcherType::ATTRIBUTE;
        }

        if ($targetNode instanceof Parameter) {
            foreach ($targetNode->getChildTokens() as $token) {
                if (in_array($token->kind, [
                    TokenKind::PublicKeyword,
                    TokenKind::ProtectedKeyword,
                    TokenKind::PrivateKeyword,
                ], true)) {
                    return NameSearcherType::ATTRIBUTE_TARGET_PROMOTED_PROPERTY;
                }
            }
        }

        return match($targetNode::class) {
            ClassDeclaration::class => NameSearcherType::ATTRIBUTE_TARGET_CLASS,
            FunctionDeclaration::class => NameSearcherType::ATTRIBUTE_TARGET_FUNCTION,
            MethodDeclaration::class => NameSearcherType::ATTRIBUTE_TARGET_METHOD,
            PropertyDeclaration::class => NameSearcherType::ATTRIBUTE_TARGET_PROPERTY,
            ClassConstDeclaration::class => NameSearcherType::ATTRIBUTE_TARGET_CLASS_CONSTANT,
            Parameter::class => NameSearcherType::ATTRIBUTE_TARGET_PARAMETER,
            default => NameSearcherType::ATTRIBUTE,
        };
    }
}
