<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

final class EnumPrototype extends ClassLikePrototype
{
    public function __construct(
        string $name,
        private Cases $cases,
        Methods $methods = null,
        UpdatePolicy $updatePolicy = null
    ) {
        parent::__construct(name: $name, methods: $methods, updatePolicy: $updatePolicy);
    }

    public function cases(): Cases
    {
        return $this->cases;
    }
}
