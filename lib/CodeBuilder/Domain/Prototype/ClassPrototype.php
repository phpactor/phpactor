<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

final class ClassPrototype extends ClassLikePrototype
{

    /**
     * @var ExtendsClass
     */
    private $extendsClass;

    /**
     * @var ImplementsInterfaces
     */
    private $implementsInterfaces;

    /**
     * @var ExtendsClass
     */
    private $extendsclasss;

    public function __construct(
        string $name,
        Properties $properties = null,
        Constants $constants = null,
        Methods $methods = null,
        ExtendsClass $extendsClass = null,
        ImplementsInterfaces $implementsInterfaces = null,
        UpdatePolicy $updatePolicy = null
    ) {
        parent::__construct($name, $methods, $properties, $constants, $updatePolicy);
        $this->extendsClass = $extendsClass ?: ExtendsClass::none();
        $this->implementsInterfaces = $implementsInterfaces ?: ImplementsInterfaces::empty();
    }

    public function extendsClass(): ExtendsClass
    {
        return $this->extendsClass;
    }

    public function implementsInterfaces(): ImplementsInterfaces
    {
        return $this->implementsInterfaces;
    }
}
