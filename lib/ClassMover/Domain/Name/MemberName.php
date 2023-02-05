<?php

namespace Phpactor\ClassMover\Domain\Name;

class MemberName extends Label
{
    public function matches(string $name): bool
    {
        $compare = ltrim($name, '$');
        $thisName = ltrim((string) $this, '$');

        if ($thisName == $compare) {
            return true;
        }

        return false;
    }
}
