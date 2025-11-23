<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Domain\Prototype;

use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\Prototype\TraitPrototype;
use Phpactor\CodeBuilder\Domain\Prototype\Traits;

class TraitsTest extends TestCase
{
    #[TestDox('Create from traits')]
    public function testCreateFromTraits(): void
    {
        $traits = Traits::fromTraits([
            new TraitPrototype('One'),
            new TraitPrototype('Two'),
        ]);
        $this->assertCount(2, iterator_to_array($traits));
    }
}
