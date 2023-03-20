<?php

namespace Phpactor\WorseReflection\Core\Inference\Walker;

use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Diagnostics;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Phpactor\WorseReflection\Core\Inference\FrameStack;
use Phpactor\WorseReflection\Core\Inference\Walker;

class DiagnosticsWalker implements Walker
{
    /**
     * @var Diagnostic[]
     */
    private array $diagnostics = [];

    /**
     * @param DiagnosticProvider[] $providers
     */
    public function __construct(private array $providers)
    {
    }

    public function nodeFqns(): array
    {
        return [];
    }

    public function enter(FrameResolver $resolver, FrameStack $frameStack, Node $node): void
    {
        $resolver = $resolver->resolver();
        foreach ($this->providers as $provider) {
            foreach ($provider->enter($resolver, $frameStack->current(), $node) as $diagnostic) {
                $this->diagnostics[] = $diagnostic;
            }
        }

        return;
    }

    /**
     * @return Diagnostics<Diagnostic>
     */
    public function diagnostics(): Diagnostics
    {
        return new Diagnostics($this->diagnostics);
    }

    public function exit(FrameResolver $resolver, FrameStack $frameStack, Node $node): void
    {
        $resolver = $resolver->resolver();
        foreach ($this->providers as $provider) {
            foreach ($provider->exit($resolver, $frameStack->current(), $node) as $diagnostic) {
                $this->diagnostics[] = $diagnostic;
            }
        }

        return;
    }
}
