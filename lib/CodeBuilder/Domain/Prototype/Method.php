<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

final class Method extends Prototype
{
    const IS_STATIC = 1;
    const IS_ABSTRACT = 2;

    private readonly Visibility $visibility;

    private readonly Parameters $parameters;

    private readonly ReturnType $returnType;

    /*
     * @var Docblock
     */
    private $docblock;

    private readonly bool $isStatic;

    private readonly bool $isAbstract;

    private readonly MethodBody $methodBody;

    public function __construct(
        private readonly string $name,
        ?Visibility $visibility = null,
        ?Parameters $parameters = null,
        ?ReturnType $returnType = null,
        ?Docblock $docblock = null,
        int $modifierFlags = 0,
        ?MethodBody $methodBody = null,
        ?UpdatePolicy $updatePolicy = null
    ) {
        parent::__construct($updatePolicy);
        $this->visibility = $visibility ?? Visibility::public();
        $this->parameters = $parameters ?? Parameters::empty();
        $this->returnType = $returnType ?? ReturnType::none();
        $this->docblock = $docblock ?? Docblock::none();
        $this->isStatic = (bool)($modifierFlags & self::IS_STATIC);
        $this->isAbstract = (bool)($modifierFlags & self::IS_ABSTRACT);
        $this->methodBody = $methodBody ?? MethodBody::empty();
    }

    public function name(): string
    {
        return $this->name;
    }

    public function visibility(): Visibility
    {
        return $this->visibility;
    }

    public function parameters(): Parameters
    {
        return $this->parameters;
    }

    public function returnType(): ReturnType
    {
        return $this->returnType;
    }

    public function docblock(): Docblock
    {
        return $this->docblock;
    }

    public function isStatic(): bool
    {
        return $this->isStatic;
    }

    public function isAbstract(): bool
    {
        return $this->isAbstract;
    }

    public function body(): MethodBody
    {
        return $this->methodBody;
    }
}
