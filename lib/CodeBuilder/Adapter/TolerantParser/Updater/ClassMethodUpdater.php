<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Updater;

use Microsoft\PhpParser\Node\ClassMembersNode;
use Microsoft\PhpParser\ClassLike;
use Microsoft\PhpParser\Node\EnumMembers;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Node\InterfaceMembers;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\EnumDeclaration;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Microsoft\PhpParser\Node\TraitMembers;
use Phpactor\CodeBuilder\Domain\Renderer;
use Phpactor\CodeBuilder\Domain\Prototype\Method;
use RuntimeException;
use Microsoft\PhpParser\Node;
use Webmozart\Assert\Assert;

/**
 * @extends AbstractMethodUpdater<ClassMembersNode|TraitMembers>
 */
class ClassMethodUpdater extends AbstractMethodUpdater
{
    public function memberDeclarationsNode(ClassLike|ObjectCreationExpression $classNode): ClassMembersNode|TraitMembers|EnumMembers|InterfaceMembers
    {
        if ($classNode instanceof ClassDeclaration || $classNode instanceof ObjectCreationExpression) {
            $classNode = $classNode->classMembers;
            Assert::isInstanceOf($classNode, ClassMembersNode::class);

            return $classNode;
        }
        if ($classNode instanceof TraitDeclaration) {
            return $classNode->traitMembers;
        }
        if ($classNode instanceof EnumDeclaration) {
            return $classNode->enumMembers;
        }

        throw new RuntimeException(sprintf(
            'Can not get member declarations for "%s"',
            get_class($classNode)
        ));
    }

    public function renderMethod(Renderer $renderer, Method $method): string
    {
        return $renderer->render($method) .
            "\n" .
            $renderer->render($method->body());
    }

    /** @return array<Node> */
    protected function memberDeclarations(ClassLike|ObjectCreationExpression $classNode): array
    {
        if ($classNode instanceof ClassDeclaration || $classNode instanceof ObjectCreationExpression) {
            return $classNode->classMembers->classMemberDeclarations ?? [];
        }
        if ($classNode instanceof TraitDeclaration) {
            return $classNode->traitMembers->traitMemberDeclarations;
        }
        if ($classNode instanceof EnumDeclaration) {
            return $classNode->enumMembers->enumMemberDeclarations;
        }

        throw new RuntimeException(sprintf(
            'Can not get member declarations for "%s"',
            get_class($classNode)
        ));
    }
}
