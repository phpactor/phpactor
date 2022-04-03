<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Core\Types;

final class Variable
{
    private string $name;
    
    private NodeContext $symbolContext;

    private function __construct(string $name, NodeContext $symbolContext)
    {
        $this->name = $name;
        $this->symbolContext = $symbolContext;
    }

    public function __toString()
    {
        return $this->name;
    }

    public static function fromSymbolContext(NodeContext $symbolContext): Variable
    {
        return new self(
            $symbolContext->symbol()->name(),
            $symbolContext
        );
    }

    public function name(): string
    {
        return $this->name;
    }

    public function symbolContext(): NodeContext
    {
        return $this->symbolContext;
    }

    public function isNamed(string $name): bool
    {
        $name = ltrim($name, '$');

        return $this->name == $name;
    }

    public function withTypes(Types $types): self
    {
        return new self($this->name, $this->symbolContext->withTypes($types));
    }
}
