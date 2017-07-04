<?php

namespace Phpactor\Tests\Unit\UserInterface\Console\Dumper;

use PHPUnit\Framework\TestCase;
use Phpactor\UserInterface\Console\Dumper\DumperRegistry;
use Phpactor\UserInterface\Console\Dumper\Dumper;

class DumperRegistryTest extends TestCase
{
    /**
     * @testdox It throws exception if dumper not found.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown dumper "foobar", known dumpers: "dumper1"
     */
    public function testNotFound()
    {
        $registry = $this->create([
            'dumper1' => $this->prophesize(Dumper::class)->reveal(),
        ]);

        $registry->get('foobar');
    }

    /**
     * @testdox It returns the requested dumper.
     */
    public function testGetDumper()
    {
        $registry = $this->create([
            'foobar' => $dumper = $this->prophesize(Dumper::class)->reveal(),
        ]);

        $this->assertSame($dumper, $registry->get('foobar'));
    }

    private function create(array $dumpers)
    {
        return new DumperRegistry($dumpers);
    }
}
