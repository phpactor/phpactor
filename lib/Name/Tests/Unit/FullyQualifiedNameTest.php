<?php

namespace Phpactor\Name\Tests\Unit;

use Phpactor\Name\FullyQualifiedName;

class FullyQualifiedNameTest extends AbstractQualifiedNameTestCase
{
    protected function createFromArray(array $parts)
    {
        return FullyQualifiedName::fromArray($parts);
    }

    protected function createFromString(string $string)
    {
        return FullyQualifiedName::fromString($string);
    }
}
