<?php

namespace Phpactor\WorseReflection\Core;

use Microsoft\PhpParser\Node;

class NodeContextVisitors
{
    /**
     * @param array<class-string<Node>,list<NodeContextVisitor>> $visitors
     */
    private function __construct(private array $visitors)
    {
    }

    /**
     * @param class-string<Node> $nodeClass
     * @return NodeContextVisitor[]
     */
    public function visitorsFor(string $nodeClass): array
    {
        if (!isset($this->visitors[$nodeClass])) {
            return [];
        }
        return $this->visitors[$nodeClass];
    }

    public static function fromVisitors(NodeContextVisitor ...$visitors): self
    {
        $byClass = [];
        foreach ($visitors as $visitor) {
            foreach ($visitor->fqns() as $fqn) {
                if (!isset($byClass[$fqn])) {
                    $byClass[$fqn] = [];
                }
                $byClass[$fqn][] = $visitor;
            }
        }

        return new self($byClass);
    }
}
