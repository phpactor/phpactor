<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

final class Property extends Prototype
{
    private Visibility $visibility;

    private DefaultValue $defaultValue;

    private Type $type;

    private Type $docType;

    public function __construct(
        private string $name,
        ?Visibility $visibility = null,
        ?DefaultValue $defaultValue = null,
        ?Type $type = null,
        ?Type $docType = null,
        ?UpdatePolicy $updatePolicy = null
    ) {
        parent::__construct($updatePolicy);
        $this->visibility = $visibility ?: Visibility::public();
        $this->defaultValue = $defaultValue ?: DefaultValue::none();
        $this->type = $type ?: Type::none();
        $this->docType = $docType ?: Type::none();
        $this->updatePolicy = $updatePolicy;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function visibility(): Visibility
    {
        return $this->visibility;
    }

    public function defaultValue(): DefaultValue
    {
        return $this->defaultValue;
    }

    public function type(): Type
    {
        return $this->type;
    }

    public function docTypeOrType(): Type
    {
        if ($this->docType->notNone()) {
            return $this->docType;
        }

        return $this->type;
    }

    public function docType(): Type
    {
        return $this->docType;
    }

    public function docTypeAddsAdditionalInfo(): bool
    {
        return (string)$this->docType !== (string)$this->type;
    }
}
