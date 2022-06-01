<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Updater;

use Microsoft\PhpParser\ClassLike;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Phpactor\CodeBuilder\Domain\Renderer;
use Phpactor\CodeBuilder\Domain\Prototype\Method;
use RuntimeException;

class ClassMethodUpdater extends AbstractMethodUpdater
{
    public function memberDeclarationsNode(ClassLike $classNode)
    {
        if ($classNode instanceof ClassDeclaration) {
            return $classNode->classMembers;
        }
        if ($classNode instanceof TraitDeclaration) {
            return $classNode->traitMembers;
        }

        throw new RuntimeException(sprintf(
            'Cnanot get member declarations for "%s"',
            get_class($classNode)
        ));
    }

    public function renderMethod(Renderer $renderer, Method $method)
    {
        return $renderer->render($method) .
            PHP_EOL .
            $renderer->render($method->body());
    }

    protected function memberDeclarations(ClassLike $classNode)
    {
        if ($classNode instanceof ClassDeclaration) {
            return $classNode->classMembers->classMemberDeclarations;
        }
        if ($classNode instanceof TraitDeclaration) {
            return $classNode->traitMembers->traitMemberDeclarations;
        }

        throw new RuntimeException(sprintf(
            'Cnanot get member declarations for "%s"',
            get_class($classNode)
        ));
    }
}
