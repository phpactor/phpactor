<?php

namespace Phpactor\ConfigLoader\Tests\Unit\Adapter\PathCandidate;

use PHPUnit\Framework\TestCase;
use Phpactor\ConfigLoader\Adapter\PathCandidate\AbsolutePathCandidate;
use RuntimeException;

class AbsolutePathCandidateTest extends TestCase
{
    public function testExceptionifNotAbsolute(): void
    {
        $this->expectException(RuntimeException::class);
        new AbsolutePathCandidate('hello', 'foo');
    }
}
