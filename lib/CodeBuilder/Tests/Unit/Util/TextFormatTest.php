<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Util;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Util\TextFormat;
use RuntimeException;

class TextFormatTest extends TestCase
{
    /**
     * @dataProvider provideRemoveIndentation
     */
    public function testRemoveIndentation(string $text, string $expeced): void
    {
        self::assertEquals($expeced, (new TextFormat())->indentRemove($text));
    }

    public function provideRemoveIndentation()
    {
        yield 'empty' => [
            '',
            ''
        ];

        yield 'uniform' => [
            <<<'EOT'
                  asd
                  asd
                  asd
                EOT
           ,
            <<<'EOT'
                asd
                asd
                asd
                EOT
        ];

        yield 'tabs' => [
            <<<EOT
                \tasd
                \tasd
                \tasd
                EOT
           ,
            <<<'EOT'
                asd
                asd
                asd
                EOT
        ];

        yield 'different indentations' => [
            <<<'EOT'
                  asd
                    asd
                  asd
                EOT
           ,
            <<<'EOT'
                asd
                asd
                asd
                EOT
        ];

        yield 'code' => [
            <<<'EOT'
                class Foo
                {
                    public function bar()
                    {
                        echo $hello;
                    }
                }
                EOT
           ,
            <<<'EOT'
                class Foo
                {
                public function bar()
                {
                echo $hello;
                }
                }
                EOT
        ];

        yield 'preserve new line' => [
            <<<'EOT'

                class Foo
                {
                    public function bar()
                    {
                        echo $hello;
                    }
                }
                EOT
           ,
           <<<'EOT'

               class Foo
               {
               public function bar()
               {
               echo $hello;
               }
               }
               EOT
        ];
    }

    /**
     * @dataProvider provideIndent
     */
    public function testIndent(string $text, int $level, string $expected): void
    {
        self::assertEquals($expected, (new TextFormat())->indent($text, $level));
    }

    public function provideIndent()
    {
        yield 'empty' => [
            '',
            0,
            ''
        ];

        yield 'exmaple 1' => [
            <<<'EOT'
                private $bar;
                EOT
            ,
            1,
           <<<'EOT'
                   private $bar;
               EOT
        ];
    }

    public function testIndentExceptionIfLevelLessThan0(): void
    {
        $this->expectException(RuntimeException::class);
        (new TextFormat())->indent('foobar', -1);
    }

    public function testReplacesIndentation(): void
    {
        $this->assertEquals(<<<'EOT'
                foo
                bar
            EOT
        , (new TextFormat())->indentReplace(<<<'EOT'
              foo
              bar
            EOT
        , 1));
    }
}
