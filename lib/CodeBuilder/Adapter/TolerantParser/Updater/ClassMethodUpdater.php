<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Updater;

use Microsoft\PhpParser\Node\ClassMembersNode;
use Microsoft\PhpParser\ClassLike;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Microsoft\PhpParser\Node\TraitMembers;
use Phpactor\CodeBuilder\Domain\Renderer;
use Phpactor\CodeBuilder\Domain\Prototype\Method;
use RuntimeException;
use Microsoft\PhpParser\Node;

/**
 * @extends AbstractMethodUpdater<ClassMembersNode|TraitMembers>
 */
class ClassMethodUpdater extends AbstractMethodUpdater
{
    /**
    * @return ClassMembersNode|TraitMembers
    */
    public function memberDeclarationsNode(ClassLike $classNode)
    {
        if ($classNode instanceof ClassDeclaration) {
            return $classNode->classMembers;
        }
        if ($classNode instanceof TraitDeclaration) {
            return $classNode->traitMembers;
        }

        throw new RuntimeException(sprintf(
            'Can not get member declarations for "%s"',
            get_class($classNode)
        ));
    }

    public function renderMethod(Renderer $renderer, Method $method): string
    {
        return $renderer->render($method) .
            PHP_EOL .
            $renderer->render($method->body());
    }

    /** @return array<Node> */
    protected function memberDeclarations(ClassLike $classNode): array
    {
        if ($classNode instanceof ClassDeclaration) {
            return $classNode->classMembers->classMemberDeclarations;
        }
        if ($classNode instanceof TraitDeclaration) {
            return $classNode->traitMembers->traitMemberDeclarations;
        }

        throw new RuntimeException(sprintf(
            'Can not get member declarations for "%s"',
            get_class($classNode)
        ));
    }
}
