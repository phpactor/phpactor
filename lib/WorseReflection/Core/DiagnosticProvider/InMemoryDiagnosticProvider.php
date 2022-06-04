<?php

namespace Phpactor\WorseReflection\Core\DiagnosticProvider;

use Generator;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;

class InMemoryDiagnosticProvider implements DiagnosticProvider
{
    /**
     * @var Diagnostic[]
     */
    private array $diagnostics;

    /**
     * @param Diagnostic[] $diagnostics
     */
    public function __construct(array $diagnostics)
    {
        $this->diagnostics = $diagnostics;
    }
    public function provide(NodeContextResolver $resolver, Frame $frame, Node $node): Generator
    {
        foreach ($this->diagnostics as $diagnostic) {
            yield $diagnostic;
        }
    }
}
