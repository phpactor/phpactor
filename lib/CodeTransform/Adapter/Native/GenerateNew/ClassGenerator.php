<?php

namespace Phpactor\CodeTransform\Adapter\Native\GenerateNew;

use Phpactor\CodeTransform\Domain\GenerateNew;
use Phpactor\CodeTransform\Domain\ClassName;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\CodeBuilder\Domain\Renderer;

class ClassGenerator implements GenerateNew
{
    public function __construct(
        private Renderer $renderer,
        private ?string $variant = null
    ) {
    }


    public function generateNew(ClassName $targetName): SourceCode
    {
        $builder = SourceCodeBuilder::create();
        $builder->namespace($targetName->namespace());
        $classPrototype = $builder->class($targetName->short());

        return SourceCode::fromString(
            (string) $this->renderer->render($builder->build(), $this->variant)
        );
    }
}
