<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Updater;

use Microsoft\PhpParser\ClassLike;
use Phpactor\CodeBuilder\Domain\Prototype\Method;
use Phpactor\CodeBuilder\Domain\Renderer;

class InterfaceMethodUpdater extends AbstractMethodUpdater
{
    public function memberDeclarationsNode(ClassLike $classNode)
    {
        return $classNode->interfaceMembers;
    }

    public function renderMethod(Renderer $renderer, Method $method)
    {
        return $renderer->render($method) . ';';
    }

    protected function memberDeclarations(ClassLike $classNode)
    {
        return $classNode->interfaceMembers->interfaceMemberDeclarations;
    }
}
