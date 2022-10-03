<?php

namespace Phpactor\ConfigLoader\Tests\Unit\Adapter\PathCandidate;

use Phpactor\ConfigLoader\Adapter\PathCandidate\XdgPathCandidate;
use PHPUnit\Framework\TestCase;

class XdgPathCandidateTest extends TestCase
{
    public function testCandidate(): void
    {
        $candidate = new XdgPathCandidate('phpactor', 'phpactor', 'foo');
        $this->assertStringContainsString('phpactor', $candidate->path());
        $this->assertEquals('foo', $candidate->loader());
    }
}
