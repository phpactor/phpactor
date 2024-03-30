<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Updater;

use Microsoft\PhpParser\ClassLike;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Node\InterfaceMembers;
use Phpactor\CodeBuilder\Domain\Renderer;
use Phpactor\CodeBuilder\Domain\Prototype\Method;
use Microsoft\PhpParser\Node;

/**
 * @extends AbstractMethodUpdater<InterfaceMembers>
 */
class InterfaceMethodUpdater extends AbstractMethodUpdater
{
    public function memberDeclarationsNode(ClassLike|ObjectCreationExpression $classNode): InterfaceMembers
    {
        return $classNode->interfaceMembers;
    }

    public function renderMethod(Renderer $renderer, Method $method): string
    {
        return $renderer->render($method) . ';';
    }

    /** @return array<Node> */
    protected function memberDeclarations(ClassLike|ObjectCreationExpression $classNode): array
    {
        return $classNode->interfaceMembers?->interfaceMemberDeclarations ?? [];
    }
}
