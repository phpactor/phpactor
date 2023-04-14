<?php

namespace Phpactor\WorseReflection\Core\Inference\Walker;

use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Diagnostics;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
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

    public function enter(FrameResolver $resolver, Frame $frame, Node $node): Frame
    {
        $resolver = $resolver->resolver();
        foreach ($this->providers as $provider) {
            foreach ($provider->enter($resolver, $frame, $node) as $diagnostic) {
                $this->diagnostics[] = $diagnostic;
            }
        }

        return $frame;
    }

    /**
     * @return Diagnostics<Diagnostic>
     */
    public function diagnostics(): Diagnostics
    {
        return new Diagnostics($this->diagnostics);
    }

    public function exit(FrameResolver $resolver, Frame $frame, Node $node): Frame
    {
        $resolver = $resolver->resolver();
        foreach ($this->providers as $provider) {
            foreach ($provider->exit($resolver, $frame, $node) as $diagnostic) {
                $this->diagnostics[] = $diagnostic;
            }
        }

        return $frame;
    }
}
