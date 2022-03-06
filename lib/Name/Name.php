<?php

namespace Phpactor\Name;

use Countable;

interface Name extends Countable
{
    public function __toString(): string;

    public function toArray(): array;

    public function head(): QualifiedName;

    public function tail(): Name;

    public function isDescendantOf(Name $name): bool;

    public function count(): int;

    public function prepend(Name $name): Name;

    public function append(Name $name): Name;
}
