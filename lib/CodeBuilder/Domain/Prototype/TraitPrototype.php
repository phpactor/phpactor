<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

final class TraitPrototype extends ClassLikePrototype
{
    public function __construct(
        string $name,
        ?Properties $properties = null,
        ?Constants $constants = null,
        ?Methods $methods = null,
        ?UpdatePolicy $updatePolicy = null
    ) {
        parent::__construct($name, $methods, $properties, $constants, $updatePolicy);
    }
}
