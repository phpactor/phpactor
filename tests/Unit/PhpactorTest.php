<?php

namespace Phpactor\Tests\Unit;

use Phpactor\Phpactor;
use PHPUnit\Framework\TestCase;

class PhpactorTest extends TestCase
{
    /**
     * @testdox It returns true if the subject looks like a file.
     * @dataProvider provideIsFile
     */
    public function testIsFile(string $example, bool $isFile): void
    {
        $this->assertEquals($isFile, Phpactor::isFile($example));
    }

    public function provideIsFile()
    {
        return [
            [
                'Hello.php',
                true
            ],
            [
                'Hello\\Bar',
                false
            ],
            [
                'Hello',
                false
            ],
            [
                './Hello/Bar',
                true
            ],
            [
                'Foobar/*',
                true
            ],
            [
                'lib/Badger.php',
                true
            ]
        ];
    }
}
