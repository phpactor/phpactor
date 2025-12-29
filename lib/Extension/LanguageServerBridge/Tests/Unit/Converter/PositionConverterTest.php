<?php

namespace Phpactor\Extension\LanguageServerBridge\Tests\Unit\Converter;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\TextDocument\ByteOffset;

class PositionConverterTest extends TestCase
{
    public function testPositionToByteOffset(): void
    {
        self::assertEquals(
            ByteOffset::fromInt(15),
            PositionConverter::positionToByteOffset(
                new Position(2, 3),
                <<<'EOT'
                    Hello
                    Carld
                    World
                    Farld
                    EOT
            )
        );
        self::assertEquals(
            ByteOffset::fromInt(39),
            PositionConverter::positionToByteOffset(
                new Position(2, 3),
                <<<'EOT'
                    👩👨👦👧
                    👩👨👦👧
                    👩👨👦👧
                    👩👨👦👧
                    EOT
            )
        );

        self::assertEquals(
            ByteOffset::fromInt(45),
            PositionConverter::positionToByteOffset(
                new Position(2, 30),
                <<<'PHP'
                    <?php

                    echo '👩👨👦👧' . invalid() . strlen('Lorem ipsum dolor sit amet');
                    PHP
            )
        );
    }

    public function testWhenOutOfBoundsAssumeEndOfDocument(): void
    {
        self::assertEquals(
            new Position(0, 10),
            PositionConverter::byteOffsetToPosition(
                ByteOffset::fromInt(20),
                '0123456789'
            )
        );
    }

    public function testUtf16(): void
    {
        self::assertEquals(
            new Position(0, 0),
            PositionConverter::byteOffsetToPosition(
                ByteOffset::fromInt(0),
                'a𐐀b'
            )
        );

        self::assertEquals(
            new Position(0, 1),
            PositionConverter::byteOffsetToPosition(
                ByteOffset::fromInt(1),
                'a𐐀b'
            )
        );
        self::assertEquals(
            new Position(0, 2),
            PositionConverter::byteOffsetToPosition(
                ByteOffset::fromInt(2),
                'a𐐀b'
            )
        );
    }
}
