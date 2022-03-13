<?php

namespace Phpactor\Name\Tests\Unit;

use Phpactor\Name\FullyQualifiedName;
use Phpactor\Name\QualifiedName;

class QualifiedNameTest extends AbstractQualifiedNameTestCase
{
    public function testCanBeConvertedToFullyQualifiedName(): void
    {
        $this->assertEquals(
            FullyQualifiedName::fromString('Foobar\\Barfoo'),
            $this->createFromString('Foobar\\Barfoo')->toFullyQualifiedName()
        );
    }

    protected function createFromArray(array $parts)
    {
        return QualifiedName::fromArray($parts);
    }

    protected function createFromString(string $string): QualifiedName
    {
        return QualifiedName::fromString($string);
    }
}
