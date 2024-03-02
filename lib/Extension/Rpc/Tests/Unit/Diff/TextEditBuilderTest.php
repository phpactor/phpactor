<?php

namespace Phpactor\Extension\Rpc\Tests\Unit\Diff;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\Rpc\Diff\TextEditBuilder;

class TextEditBuilderTest extends TestCase
{
    /**
     * @dataProvider provideDiff
     */
    public function testDiff(string $one, string $two, array $expected): void
    {
        $textEditBuilder = new TextEditBuilder();
        $chunks = $textEditBuilder->calculateTextEdits($one, $two);
        $this->assertEquals($expected, $chunks);
    }

    public function provideDiff()
    {
        yield 'no edits' => [
            <<<'EOT'
                original
                original
                original
                EOT
            ,
            <<<'EOT'
                original
                original
                original
                EOT
        ,
            [ ]
        ];

        yield 'addition at start of file' => [
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
                    'start' => [ 'line' => 0, 'character' => 0 ],
                    'end' => [ 'line' => 0, 'character' => 0 ],
                    'text' => 'new' . "\n",
                ],
            ],
        ];

        yield 'first line changed' => [
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
                    'start' => [ 'line' => 0, 'character' => 0 ],
                    'end' => [ 'line' => 1, 'character' => 0 ],
                    'text' => '',
                ],
                [
                    'start' => [ 'line' => 0, 'character' => 0 ],
                    'end' => [ 'line' => 0, 'character' => 0 ],
                    'text' => 'neworiginal' . "\n",
                ],
            ],
        ];

        yield 'last line changed' => [
            <<<'EOT'
                original
                original
                middle
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
                    'start' => [ 'line' => 2, 'character' => 0 ],
                    'end' => [ 'line' => 3, 'character' => 0 ],
                    'text' => '',
                ],
                [
                    'start' => [ 'line' => 2, 'character' => 0 ],
                    'end' => [ 'line' => 2, 'character' => 0 ],
                    'text' => 'original',
                ],
            ],
        ];
    }
}
