<?php

namespace Phpactor\Tests\Unit\UserInterface\Console\Dumper;

use Phpactor\Console\Dumper\IndentedDumper;

class IndentedDumperTest extends DumperTestCase
{
    protected function dumper()
    {
        return new IndentedDumper();
    }

    /**
     * @testdox It outputs indented dump
     */
    public function testIndentedOutput()
    {
        $output = $this->dump([
            'hello' => 'test',
            'one' => [
                'two' => 3,
                'four' => 5,
                'size' => [
                    'seven' => 'eight',
                ]
            ],
            'two' => [
                'hai' => 'ho',
            ],
        ]);
        $this->assertEquals(<<<'EOT'
hello:test
one:
  two:3
  four:5
  size:
    seven:eight
two:
  hai:ho

EOT
        , $output);
    }
}
