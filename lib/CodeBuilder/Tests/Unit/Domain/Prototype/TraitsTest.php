<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Domain\Prototype;

use Phpactor\CodeBuilder\Domain\Prototype\TraitPrototype;
use Phpactor\CodeBuilder\Domain\Prototype\Traits;
use PHPUnit\Framework\TestCase;

class TraitsTest extends TestCase
{
    /**
     * @testdox Create from traits
     */
    public function testCreateFromTraits(): void
    {
        $traits = Traits::fromTraits([
            new TraitPrototype('One'),
            new TraitPrototype('Two'),
        ]);
        $this->assertCount(2, iterator_to_array($traits));
    }
}
