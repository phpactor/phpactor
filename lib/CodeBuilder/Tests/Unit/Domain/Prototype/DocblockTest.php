<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Domain\Prototype;

use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\Prototype\Docblock;

class DocblockTest extends TestCase
{
    #[TestDox('It returns docblock as lines.')]
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
