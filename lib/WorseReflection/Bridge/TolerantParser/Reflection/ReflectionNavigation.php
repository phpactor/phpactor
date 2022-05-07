<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\SourceFileNode;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\NavigatorElementCollection;
use Phpactor\WorseReflection\Core\ServiceLocator;

class ReflectionNavigation
{
    private SourceFileNode $node;

    private ServiceLocator $locator;

    public function __construct(ServiceLocator $locator, SourceFileNode $node)
    {
        $this->node = $node;
        $this->locator = $locator;
    }

    /**
     * @return NavigatorElementCollection<ReflectionMethodCall>
     */
    public function methodCalls(): NavigatorElementCollection
    {
        $calls = [];
        foreach ($this->node->getDescendantNodes() as $node) {
            if ($node instanceof MemberAccessExpression) {
                if (!$node->parent instanceof CallExpression) {
                    continue;
                }
                $calls[] = new ReflectionMethodCall($this->locator, new Frame('test'), $node);
            }
        }
        return new NavigatorElementCollection($calls);
    }

    /**
     * @return NavigatorElementCollection<ReflectionPropertyAccess>
     */
    public function propertyAccesses(): NavigatorElementCollection
    {
        $elements = [];
        foreach ($this->node->getDescendantNodes() as $node) {
            if (!$node instanceof MemberAccessExpression) {
                continue;
            }
            if ($node->parent instanceof CallExpression) {
                continue;
            }

            $elements[] = new ReflectionPropertyAccess($node);
        }
        return new NavigatorElementCollection($elements);
    }

    /**
     * @return NavigatorElementCollection<ReflectionConstantAccess>
     */
    public function constantAccesses(): NavigatorElementCollection
    {
        $elements = [];
        foreach ($this->node->getDescendantNodes() as $node) {
            if (!$node instanceof ScopedPropertyAccessExpression) {
                continue;
            }
            if ($node->parent instanceof CallExpression) {
                continue;
            }

            $elements[] = new ReflectionConstantAccess($node);
        }
        return new NavigatorElementCollection($elements);
    }
}
