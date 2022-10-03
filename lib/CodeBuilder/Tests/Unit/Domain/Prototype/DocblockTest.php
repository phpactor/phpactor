<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Domain\Prototype;

use Phpactor\CodeBuilder\Domain\Prototype\Docblock;
use PHPUnit\Framework\TestCase;

class DocblockTest extends TestCase
{
    /**
     * @testdox It returns docblock as lines.
     */
    public function testAsLines(): void
    {
        $this->assertEquals([''], Docblock::fromString('')->asLines());
        $this->assertEquals(['One', 'Two'], Docblock::fromString(
            <<<'EOT'
                One
                Two
                EOT
        )->asLines());
    }
}
