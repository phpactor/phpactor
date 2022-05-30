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
     * @var DiagnosticProvider[]
     */
    private array $providers;

    /**
     * @param DiagnosticProvider[] $providers
     */
    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }

    public function nodeFqns(): array
    {
        return [];
    }

    public function enter(FrameResolver $resolver, Frame $frame, Node $node): Frame
    {
        return $frame;
    }

    public function diagnostics(): Diagnostics
    {
        return new Diagnostics($this->diagnostics);
    }

    public function exit(FrameResolver $resolver, Frame $frame, Node $node): Frame
    {
        $resolver = $resolver->resolver();
        foreach ($this->providers as $provider) {
            foreach ($provider->provide($resolver, $frame, $node) as $diagnostic) {
                $this->diagnostics[] = $diagnostic;
            }
        }

        return $frame;
    }
}
