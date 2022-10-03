<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Domain\Prototype;

use InvalidArgumentException;
use Phpactor\CodeBuilder\Domain\Prototype\Visibility;
use PHPUnit\Framework\TestCase;

class VisibilityTest extends TestCase
{
    /**
     * @testdox It throws an exception if an invalid visiblity is given.
     */
    public function testException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid visibility');
        Visibility::fromString('foobar');
    }
}
