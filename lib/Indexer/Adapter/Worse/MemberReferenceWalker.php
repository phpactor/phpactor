<?php

namespace Phpactor\Indexer\Adapter\Worse;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Token;
use Phpactor\Indexer\Model\LocationConfidence;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Phpactor\WorseReflection\Core\Inference\Walker;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class MemberReferenceWalker implements Walker
{
    private ?string $containerType;
    private string $memberName;

    /**
     * @var LocationConfidence[]
     */
    private $locations = [];

    private TextDocumentUri $uri;

    public function __construct(TextDocumentUri $uri, ?string $containerType, string $memberName)
    {
        $this->containerType = $containerType;
        $this->memberName = $memberName;
        $this->uri = $uri;
    }

    public function nodeFqns(): array
    {
        return [
            MemberAccessExpression::class,
            ScopedPropertyAccessExpression::class,
        ];
    }

    public function enter(FrameResolver $resolver, Frame $frame, Node $node): Frame
    {
        if ($node instanceof MemberAccessExpression) {
            $name = NodeUtil::nameFromTokenOrNode($node, $node->memberName);

            if ($name !== $this->memberName) {
                return $frame;
            }

            $containerType = $resolver->resolveNode($frame, $node->dereferencableExpression)->type();

            if (!$this->containerType) {
                $this->locations[] = LocationConfidence::surely($this->location($node->memberName));
                return $frame;
            }

            if (!$containerType->isDefined()) {
                $this->locations[] = LocationConfidence::maybe($this->location($node->memberName));
                return $frame;
            }

            // todo: change to use accepts and refactor other code
            if ($containerType->equals(TypeFactory::class($this->containerType))) {
                $this->locations[] = LocationConfidence::surely($this->location($node->memberName));
                return $frame;
            }

            $this->locations[] = LocationConfidence::not($this->location($node->memberName));
        }

        if ($node instanceof ScopedPropertyAccessExpression) {
            $name = NodeUtil::nameFromTokenOrNode($node, $node->memberName);

            if (ltrim($name, '$') !== $this->memberName) {
                return $frame;
            }

            $containerType = $resolver->resolveNode($frame, $node->scopeResolutionQualifier)->type();

            if (!$this->containerType) {
                $this->locations[] = LocationConfidence::surely($this->location($node->memberName));
                return $frame;
            }

            if (!$containerType->isDefined()) {
                $this->locations[] = LocationConfidence::maybe($this->location($node->memberName));
            }

            if ($containerType->accepts(TypeFactory::class($this->containerType))->isTrue()) {
                $this->locations[] = LocationConfidence::surely($this->location($node->memberName));
                return $frame;
            }

            $this->locations[] = LocationConfidence::not($this->location($node->memberName));
        }

        return $frame;
    }

    public function exit(FrameResolver $resolver, Frame $frame, Node $node): Frame
    {
        return $frame;
    }

    /**
     * @param Node|Token $nodeOrToken
     */
    private function location($nodeOrToken): Location
    {
        return new Location($this->uri, ByteOffset::fromInt($nodeOrToken->getStartPosition()));
    }

    /**
     * @return LocationConfidence[]
     */
    public function locations(): array
    {
        return $this->locations;
    }

    public function uri(): TextDocumentUri
    {
        return $this->uri;
    }
}
