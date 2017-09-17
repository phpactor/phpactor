<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Phpactor\CodeTransform\Domain\Generators;

class ClassNew implements Handler
{
    /**
     * @var Generators
     */
    private $classNewGenerators;

    public function __construct(Generators $classNewGenerators)
    {
        $this->classNewGenerators = $classNewGenerators;
    }

    public function name(): string
    {
        return 'class_generate';
    }

    public function defaultParameters(): array
    {
        return [
            'class' => null,
            'variant' => null,
        ];
    }

    public function handle(array $arguments)
    {

    }
}

