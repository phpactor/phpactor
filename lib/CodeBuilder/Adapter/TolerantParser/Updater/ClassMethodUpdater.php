<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Updater;

use Microsoft\PhpParser\ClassLike;
use Phpactor\CodeBuilder\Domain\Renderer;
use Phpactor\CodeBuilder\Domain\Prototype\Method;

class ClassMethodUpdater extends AbstractMethodUpdater
{
    public function memberDeclarationsNode(ClassLike $classNode)
    {
        return $classNode->classMembers;
    }

    public function renderMethod(Renderer $renderer, Method $method)
    {
        return $renderer->render($method) .
            PHP_EOL .
            $renderer->render($method->body());
    }

    protected function memberDeclarations(ClassLike $classNode)
    {
        return $classNode->classMembers->classMemberDeclarations;
    }
}
