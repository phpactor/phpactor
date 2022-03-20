<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\DocBlock;

use Phpactor\TestUtils\PHPUnit\TestCase;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\DocBlock\PlainDocblock;

class PlainDocblockTest extends TestCase
{
    public function testDefined(): void
    {
        self::assertFalse($this->createDocblock('')->isDefined());
        self::assertTrue($this->createDocblock('foo')->isDefined());
    }

    public function testInherits(): void
    {
        self::assertFalse($this->createDocblock('')->inherits());
        self::assertTrue($this->createDocblock('@inheritDoc')->inherits());
    }

    public function testFormatted(): void
    {
        self::assertEquals("hello world\ngoodbye world", $this->createDocblock(
            <<<'EOT'
                    /**
                     * hello world
                     * goodbye world
                     */

                EOT
        )->formatted());
    }

    private function createDocblock(string $string): DocBlock
    {
        return new PlainDocblock($string);
    }
}
