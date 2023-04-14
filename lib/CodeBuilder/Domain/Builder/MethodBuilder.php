<?php

namespace Phpactor\CodeBuilder\Domain\Builder;

use Phpactor\CodeBuilder\Domain\Prototype\Type;
use Phpactor\CodeBuilder\Domain\Prototype\UpdatePolicy;
use Phpactor\CodeBuilder\Domain\Prototype\Visibility;
use Phpactor\CodeBuilder\Domain\Prototype\Parameters;
use Phpactor\CodeBuilder\Domain\Prototype\Method;
use Phpactor\CodeBuilder\Domain\Prototype\ReturnType;
use Phpactor\CodeBuilder\Domain\Prototype\Docblock;
use Phpactor\CodeBuilder\Domain\Builder\Exception\InvalidBuilderException;

class MethodBuilder extends AbstractBuilder implements NamedBuilder
{
    protected ?Visibility $visibility = null;

    protected ?ReturnType $returnType = null;

    /**
     * @var ParameterBuilder[]
     */
    protected array $parameters = [];

    protected ?Docblock $docblock = null;

    protected bool $static = false;

    protected bool $abstract = false;

    protected MethodBodyBuilder $bodyBuilder;

    public function __construct(private ClassLikeBuilder $parent, protected string $name)
    {
        $this->bodyBuilder = new MethodBodyBuilder($this);
    }

    public static function childNames(): array
    {
        return [
            'parameters',
        ];
    }

    public function add(NamedBuilder $builder): void
    {
        if ($builder instanceof ParameterBuilder) {
            $this->parameters[$builder->builderName()] = $builder;
        }

        throw new InvalidBuilderException($this, $builder);
    }

    public function visibility(string $visibility): MethodBuilder
    {
        $this->visibility = Visibility::fromString($visibility);

        return $this;
    }

    /**
     * @param mixed $originalType
     */
    public function returnType(string $returnType, $originalType = null): MethodBuilder
    {
        $this->returnType = new ReturnType(new Type($returnType, $originalType));

        return $this;
    }

    public function parameter(string $name): ParameterBuilder
    {
        if (isset($this->parameters[$name])) {
            return $this->parameters[$name];
        }

        $this->parameters[$name] = $builder = new ParameterBuilder($this, $name);

        return $builder;
    }

    public function docblock(string $docblock): MethodBuilder
    {
        $this->docblock = Docblock::fromString($docblock);

        return $this;
    }

    public function getDocblock(): ?Docblock
    {
        return $this->docblock;
    }

    public function build(): Method
    {
        $modifiers = 0;

        if ($this->static) {
            $modifiers = $modifiers|Method::IS_STATIC;
        }

        if ($this->abstract) {
            $modifiers = $modifiers|Method::IS_ABSTRACT;
        }

        $methodBody = $this->bodyBuilder->build();

        return new Method(
            $this->name,
            $this->visibility ?? Visibility::public(),
            Parameters::fromParameters(array_map(function (ParameterBuilder $builder) {
                return $builder->build();
            }, $this->parameters)),
            $this->returnType,
            $this->docblock,
            $modifiers,
            $methodBody,
            UpdatePolicy::fromModifiedState($this->isModified())
        );
    }

    public function static(): MethodBuilder
    {
        $this->static = true;
        return $this;
    }

    public function abstract(): MethodBuilder
    {
        $this->abstract = true;
        return $this;
    }

    public function end(): ClassLikeBuilder
    {
        return $this->parent;
    }

    public function body(): MethodBodyBuilder
    {
        return $this->bodyBuilder;
    }

    public function builderName(): string
    {
        return $this->name;
    }
}
