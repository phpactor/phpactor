<?php

namespace Phpactor\Tests\Unit\Extension\Core\Console\Dumper;

use PHPUnit\Framework\Attributes\TestDox;
use Phpactor\Extension\Core\Console\Dumper\JsonDumper;

class JsonDumperTest extends DumperTestCase
{
    #[TestDox('It dumps data to JSON')]
    public function testDumpsJson(): void
    {
        $output = $this->dump(['hello' => 'test']);
        $this->assertEquals('{"hello":"test"}'."\n", $output);
    }

    protected function dumper()
    {
        return new JsonDumper();
    }
}
