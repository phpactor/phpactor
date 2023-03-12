<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

abstract class ClassLikePrototype extends Prototype
{
    private Methods $methods;

    private Properties $properties;

    private Constants $constants;

    public function __construct(
        private string $name,
        Methods $methods = null,
        Properties $properties = null,
        Constants $constants = null,
        UpdatePolicy $updatePolicy = null
    ) {
        parent::__construct($updatePolicy);
        $this->methods = $methods ?: Methods::empty();
        $this->properties = $properties ?: Properties::empty();
        $this->constants = $constants ?: Constants::empty();
    }

    public function name(): string
    {
        return $this->name;
    }

    public function methods(): Methods
    {
        return $this->methods;
    }

    public function properties(): Properties
    {
        return $this->properties;
    }

    public function constants(): Constants
    {
        return $this->constants;
    }
}
