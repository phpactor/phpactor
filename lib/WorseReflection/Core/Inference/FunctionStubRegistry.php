<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Closure;

final class FunctionStubRegistry
{
    /**
     * @var array<string,FunctionStub>
     */
    private array $functionMap;

    /**
     * @param array<string,FunctionStub> $functionMap
     */
    public function __construct(array $functionMap)
    {
        $this->functionMap = $functionMap;
    }

    /**
     * @return null|FunctionStub
     */
    public function get(string $name): ?FunctionStub
    {
        if (!isset($this->functionMap[$name])) {
            return null;
        }

        return $this->functionMap[$name];
    }
}
