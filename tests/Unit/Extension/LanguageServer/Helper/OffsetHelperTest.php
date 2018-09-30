<?php

namespace Phpactor\Tests\Unit\Extension\LanguageServer\Helper;

use LanguageServerProtocol\Position;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServer\Helper\OffsetHelper;
use Phpactor\TestUtils\ExtractOffset;

class OffsetHelperTest extends TestCase
{
    /**
     * @dataProvider provideConvertOffsetToPosition
     */
    public function testConvertOffsetToPosition(string $text, Position $expectedPosition)
    {
        [ $source, $offset] = ExtractOffset::fromSource($text);

        $position = OffsetHelper::offsetToPosition($source, (int) $offset);

        $this->assertEquals($expectedPosition, $position);
    }

    public function provideConvertOffsetToPosition()
    {
        yield [
            '<>',
            new Position(0, 0)
        ];

        yield [
            <<<'EOT'
a<>


foo
EOT
            ,
            new Position(0, 1)
        ];

        yield [
            <<<'EOT'
a


foo<>
EOT
            ,
            new Position(3, 3)
        ];
    }
}
