<?php

namespace Phpactor\Tests\Unit;

use Generator;
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

    /**
     * @return Generator<array{string,bool}>
     */
    public function provideIsFile(): Generator
    {
        yield [ 'Hello.php', true ];
        yield [ 'Hello\\Bar', false ];
        yield [ 'Hello', false ];
        yield [ './Hello/Bar', true ];
        yield [ 'Foobar/*', true ];
        yield [ 'lib/Badger.php', true ];
    }
}
