<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Updater;

use Phpactor\CodeBuilder\Domain\Renderer;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Edits;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Phpactor\CodeBuilder\Domain\Prototype\InterfacePrototype;

class InterfaceUpdater
{
    private readonly InterfaceMethodUpdater $methodUpdater;

    public function __construct(private readonly Renderer $renderer)
    {
        $this->methodUpdater = new InterfaceMethodUpdater($renderer);
    }

    public function updateInterface(
        Edits $edits,
        InterfacePrototype $classPrototype,
        InterfaceDeclaration $classNode
    ): void {
        $this->methodUpdater->updateMethods($edits, $classPrototype, $classNode);
    }
}
