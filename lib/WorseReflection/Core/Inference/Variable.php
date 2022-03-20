<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Core\Types;

final class Variable
{
    private string $name;
    
    private Offset $offset;
    
    private SymbolContext $symbolContext;

    private function __construct(string $name, Offset $offset, SymbolContext $symbolContext)
    {
        $this->name = $name;
        $this->offset = $offset;
        $this->symbolContext = $symbolContext;
    }

    public function __toString()
    {
        return $this->name;
    }

    public static function fromSymbolContext(SymbolContext $symbolContext): Variable
    {
        return new self(
            $symbolContext->symbol()->name(),
            Offset::fromInt($symbolContext->symbol()->position()->start()),
            $symbolContext
        );
    }

    public function offset(): Offset
    {
        return $this->offset;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function symbolContext(): SymbolContext
    {
        return $this->symbolContext;
    }

    public function isNamed(string $name)
    {
        $name = ltrim($name, '$');

        return $this->name == $name;
    }

    public function withTypes(Types $types)
    {
        return new self($this->name, $this->offset, $this->symbolContext->withTypes($types));
    }

    public function withOffset($offset): Variable
    {
        return new self($this->name, Offset::fromUnknown($offset), $this->symbolContext);
    }
}
