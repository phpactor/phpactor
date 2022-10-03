<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Updater;

use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Edits;
use Phpactor\CodeBuilder\Domain\Prototype\InterfacePrototype;
use Phpactor\CodeBuilder\Domain\Renderer;

class InterfaceUpdater
{
    private Renderer $renderer;

    private InterfaceMethodUpdater $methodUpdater;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
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
