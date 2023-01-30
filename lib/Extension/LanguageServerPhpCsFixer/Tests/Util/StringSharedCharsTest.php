<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer\Tests\Util;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerPhpCsFixer\Util\StringSharedChars;

class StringSharedCharsTest extends TestCase
{
    public function testStartLength(): void
    {
        self::assertEquals(5, StringSharedChars::startLength(
            'Five shared characters in front',
            'Five common characters in front'
        ));

        self::assertEquals(11, StringSharedChars::startLength(
            'Same length',
            'Same length'
        ));
    }

    public function testEndLength(): void
    {
        self::assertEquals(22, StringSharedChars::endLength(
            '22 shared characters on the end',
            '22 common characters on the end'
        ));
    }

    public function testEndPos(): void
    {
        self::assertEquals(
            21,
            StringSharedChars::endPos(
                'Index of first shared character of same ends in those strings is 21',
                'Index of first common character of same ends in those strings is 21'
            )
        );

        self::assertEquals(0, StringSharedChars::endPos('same', 'same'));
    }
}
