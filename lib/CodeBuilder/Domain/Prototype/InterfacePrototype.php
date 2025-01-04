<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

final class InterfacePrototype extends ClassLikePrototype
{
    private ExtendsInterfaces $extendsInterfaces;

    public function __construct(
        string $name,
        ?Methods $methods = null,
        ?ExtendsInterfaces $extendsInterfaces = null,
        ?UpdatePolicy $updatePolicy = null
    ) {
        parent::__construct($name, $methods, null, null, $updatePolicy);
        $this->extendsInterfaces = $extendsInterfaces ?: ExtendsInterfaces::empty();
    }

    public function extendsInterfaces(): ExtendsInterfaces
    {
        return $this->extendsInterfaces;
    }
}
