<?php

namespace Phpactor\Name\Tests\Unit;

use Phpactor\Name\Names;
use Phpactor\Name\QualifiedName;
use PHPUnit\Framework\TestCase;

class NamesTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $names = Names::fromNames([
            QualifiedName::fromString('Hello'),
            QualifiedName::fromString('Goodbye')
        ]);

        self::assertCount(2, $names);
    }

    public function testCanIterate(): void
    {
        $names = Names::fromNames([
            QualifiedName::fromString('Hello'),
            QualifiedName::fromString('Goodbye')
        ]);
        self::assertIsIterable($names);
    }
}
