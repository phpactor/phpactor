<?php

namespace Phpactor\Tests\Unit\Extension\Rpc\Differ;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\Rpc\Diff\Differ;

class DifferTest extends TestCase
{
    /**
     * @dataProvider provideDiff
     */
    public function testDiff(string $one, string $two, array $expected)
    {
        $differ = new Differ();
        $chunks = $differ->chunkDiff($one, $two);
        $this->assertEquals($expected, $chunks);
    }

    public function provideDiff()
    {
        yield [
            <<<'EOT'
original
original
original
EOT
            ,
            <<<'EOT'
new
original
original
original
EOT
        ,
            [
                [
                    'start' => 0,
                    'length' => 0,
                    'text' => 'new' . PHP_EOL,
                ],
            ],
        ];

        yield [
            <<<'EOT'
original
original
original
EOT
            ,
            <<<'EOT'
neworiginal
original
original
EOT
        ,
            [
                [
                    'start' => 0,
                    'length' => 9,
                    'text' => '',
                ],
                [
                    'start' => 0,
                    'length' => 0,
                    'text' => 'neworiginal' . PHP_EOL,
                ],
            ],
        ];

        yield [
            <<<'EOT'
original
original
middle
original
EOT
            ,
            <<<'EOT'
original
original
original
EOT
        ,
            [
                [
                    'start' => 18,
                    'length' => 7,
                    'text' => '',
                ],
            ],
        ];
    }
}
