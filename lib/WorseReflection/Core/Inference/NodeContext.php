<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Generator;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use RuntimeException;

class NodeContext
{
    public ?NodeContext $parent = null;

    private TypeAssertions $typeAssertions;

    /**
     * @var string[]
     */
    private array $issues = [];

    private Frame $frame;

    /**
     * @var NodeContext[]
     */
    private array $children = [];

    protected function __construct(
        protected Symbol $symbol,
        protected Type $type,
        protected ?Type $containerType = null,
        private ?ReflectionScope $scope = null
    ) {
        $this->typeAssertions = new TypeAssertions([]);
        $this->frame = new Frame();
    }

    public function __toString(): string
    {
        return $this->debugString();
    }

    public static function for(Symbol $symbol): NodeContext
    {
        return new self($symbol, TypeFactory::unknown());
    }

    public static function fromType(Type $type): NodeContext
    {
        return new self(Symbol::unknown(), $type);
    }

    public static function none(): NodeContext
    {
        return new self(Symbol::unknown(), new MissingType());
    }

    public function withContainerType(Type $containerType): NodeContext
    {
        $new = clone $this;
        $new->containerType = $containerType;

        return $new;
    }

    public function withTypeAssertions(TypeAssertions $typeAssertions): NodeContext
    {
        $new = clone $this;
        $new->typeAssertions = $typeAssertions;

        return $new;
    }

    public function withType(Type $type): NodeContext
    {
        $new = clone $this;
        $new->type = $type;

        return $new;
    }

    public function withTypeAssertion(TypeAssertion $typeAssertion): NodeContext
    {
        $new = clone $this;
        $new->typeAssertions = $new->typeAssertions->add($typeAssertion);

        return $new;
    }

    public function withScope(ReflectionScope $scope): NodeContext
    {
        $new = clone $this;
        $new->scope = $scope;

        return $new;
    }

    public function withIssue(string $message): NodeContext
    {
        $new = clone $this;
        $new->issues[] = $message;

        return $new;
    }

    /**
     * @param Symbol::* $symbolType
     */
    public function withSymbolType($symbolType): self
    {
        $new = clone $this;
        $new->symbol = $this->symbol->withSymbolType($symbolType);

        return $new;
    }

    public function withSymbolName(string $symbolName): self
    {
        $new = clone $this;
        $new->symbol = $this->symbol->withSymbolName($symbolName);

        return $new;
    }

    public function type(): Type
    {
        return $this->type ?? new MissingType();
    }

    public function symbol(): Symbol
    {
        return $this->symbol;
    }

    public function containerType(): Type
    {
        return $this->containerType ?: new MissingType();
    }

    /**
     * @return string[]
     */
    public function issues(): array
    {
        return $this->issues;
    }

    public function scope(): ReflectionScope
    {
        return $this->scope;
    }

    public function typeAssertions(): TypeAssertions
    {
        return $this->typeAssertions;
    }

    public function negateTypeAssertions(): self
    {
        foreach ($this->typeAssertions as $typeAssertion) {
            $typeAssertion->negate();
        }

        return $this;
    }

    /**
     * Return the frame associated with this NodeContext.
     * Note that the frame is a shared object.
     */
    public function frame(): Frame
    {
        return $this->frame;
    }

    public function withFrame(Frame $frame): self
    {
        $this->frame = $frame;
        return $this;
    }

    public function addChild(NodeContext $context): self
    {
        $this->children[] = $context;

        return $this;
    }

    public function range(): ByteOffsetRange
    {
        return $this->symbol()->position();
    }

    public function descendantContextAt(Offset $offset): ?NodeContext
    {
        $context = $this->descendantContextAtOrNull($offset);
        if (null === $context) {
            throw new RuntimeException(sprintf(
                'Could not find descendant context at offset %d (range %s)',
                $offset->toInt(),
                $this->range()->__toString()
            ));
        }
        return $context;
    }

    public function descendantContextAtOrNull(Offset $offset): ?NodeContext
    {
        $lastDescendant = $this;
        foreach ($this->children() as $child) {
            if (!$child->range()->containsOffset(ByteOffset::fromInt($offset->toInt()))) {
                continue;
            }
            return $child->descendantContextAt($offset);
        }
        return $this;
    }


    /**
     * @return Generator<int, NodeContext>
     */
    public function allDescendantContexts(): Generator
    {
        foreach ($this->children as $child) {
            yield $child;
            yield from $child->allDescendantContexts();
        }
    }

    /**
     * Replace the default context with a customised implementation.
     * This method will enforce frame consistency and other constraints.
     */
    public function replace(NodeContext $nodeContext): NodeContext
    {
        return $nodeContext->withFrame($this->frame());
    }

    public function parent(): NodeContext
    {
        if (null === $this->parent) {
            throw new RuntimeException('Node has no parent');
        }
        return $this->parent;
    }

    /**
     * @return NodeContext[]
     */
    public function children(): array
    {
        return $this->children;
    }

    protected function debugString(int $depth = 0): string
    {
        $shortName = substr($this::class, strrpos($this::class, '\\') + 1);
        $indent = str_repeat(' ', $depth);
        return sprintf(
            "%s%d:%d %s: [%s]<%s> %s\n%s%s",
            $indent,
            $this->symbol()->position()->start()->toInt(),
            $this->symbol()->position()->end()->toInt(),
            $shortName,
            $this->symbol()->symbolType(),
            $this->symbol()->name(),
            $this->type()->__toString(),
            $indent,
            implode(
                "\n",
                array_map(
                    fn (NodeContext $ctx) => $ctx->debugString($depth + 1),
                    $this->children
                )
            ),
        );
    }
}
