<?php

namespace Phpactor\CodeTransform\Tests\Unit\Domain;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeTransform\Domain\NameWithByteOffset;
use Phpactor\CodeTransform\Domain\NameWithByteOffsets;
use Phpactor\Name\QualifiedName;
use Phpactor\TextDocument\ByteOffset;

class NameWithByteOffsetsTest extends TestCase
{
    public function testReduceToUniquelyNamedItems(): void
    {
        $names = new NameWithByteOffsets(...[
            new NameWithByteOffset(
                QualifiedName::fromString('Foobar'),
                ByteOffset::fromInt(12)
            ),
            new NameWithByteOffset(
                QualifiedName::fromString('Foobar'),
                ByteOffset::fromInt(22)
            ),
            new NameWithByteOffset(
                QualifiedName::fromString('Hallo'),
                ByteOffset::fromInt(42)
            ),
        ]);

        self::assertEquals(
            new NameWithByteOffsets(...[
                new NameWithByteOffset(
                    QualifiedName::fromString('Foobar'),
                    ByteOffset::fromInt(12)
                ),
                new NameWithByteOffset(
                    QualifiedName::fromString('Hallo'),
                    ByteOffset::fromInt(42)
                ),
            ]),
            $names->onlyUniqueNames()
        );
    }
}
