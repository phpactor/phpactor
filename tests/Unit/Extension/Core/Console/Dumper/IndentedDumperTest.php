<?php

namespace Phpactor\Tests\Unit\Extension\Core\Console\Dumper;

use PHPUnit\Framework\Attributes\TestDox;
use Phpactor\Extension\Core\Console\Dumper\IndentedDumper;

class IndentedDumperTest extends DumperTestCase
{
    #[TestDox('It outputs indented dump')]
    public function testIndentedOutput(): void
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

    protected function dumper()
    {
        return new IndentedDumper();
    }
}
