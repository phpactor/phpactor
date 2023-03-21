<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Generator;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;

class NodeContext
{
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
        $shortName = substr($this::class, strrpos($this::class, '\\') + 1);
        return sprintf(
            "%d:%d %s: [%s] %s\n    %s",
            $this->symbol()->position()->start()->toInt(),
            $this->symbol()->position()->end()->toInt(),
            $shortName,
            $this->symbol()->symbolType(),
            $this->type()->isDefined() ? $this->type()->__toString() : '',
            implode(
                "\n    ",
                array_map(
                    fn (NodeContext $ctx) => $ctx->__toString(),
                    $this->children
                )
            ),
        );
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

    public function descendantContextAt(Offset $offset): self
    {
        $lastDescendant = $this;
        foreach ($this->allDescendantContexts() as $descendant) {
            if ($descendant->range()->start()->toInt() > $offset->toInt()) {
                return $lastDescendant;
            }
            $lastDescendant = $descendant;
        }
        return $lastDescendant;
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
}
