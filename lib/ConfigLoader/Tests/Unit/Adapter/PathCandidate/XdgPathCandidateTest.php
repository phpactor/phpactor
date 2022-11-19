<?php

namespace Phpactor\ConfigLoader\Tests\Unit\Adapter\PathCandidate;

use PHPUnit\Framework\TestCase;
use Phpactor\ConfigLoader\Adapter\PathCandidate\XdgPathCandidate;
use XdgBaseDir\Xdg;

class XdgPathCandidateTest extends TestCase
{
    public function testCandidate(): void
    {
        $candidate = new XdgPathCandidate('phpactor', 'phpactor', 'foo', new Xdg());
        $this->assertStringContainsString('phpactor', $candidate->path());
        $this->assertEquals('foo', $candidate->loader());
    }
}
