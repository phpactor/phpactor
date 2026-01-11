<?php

namespace Phpactor\WorseReflection\Tests\Unit\Bridge\TolerantParser\Incremental\Strategy;

use Generator;
use Phpactor\TextDocument\TextEdit;
use Phpactor\WorseReflection\Bridge\TolerantParser\Incremental\Strategy\TokenStrategy;
use Phpactor\WorseReflection\Bridge\TolerantParser\Incremental\UpdaterStrategy;
use Phpactor\WorseReflection\Tests\Unit\Bridge\TolerantParser\Incremental\UpdaterStrategyTestCase;

class TokenStrategyTest extends UpdaterStrategyTestCase
{
    public function strategy(): UpdaterStrategy
    {
        return new TokenStrategy();
    }

    public static function provideUpdate(): Generator
    {
        yield 'delete char' => [
            <<<'PHP'
                <?php
                $foobar;
                PHP,
            TextEdit::create(9, 1, ''),
            true,
        ];

        yield 'introduce char' => [
            <<<'PHP'
                <?php
                $foobar;
                PHP,
            TextEdit::create(9, 1, 'barrb'),
            true,
        ];

        yield 'introduce char prefix' => [
            <<<'PHP'
                <?php

                $foobar;
                $barfoo;
                PHP,
            TextEdit::create(7, 0, ' '),
            false,
            <<<'PHP'
                <?php

                 $foobar;
                $barfoo;
                PHP,
        ];

        yield 'introduce char prefix multi' => [
            <<<'PHP'
                <?php

                if (false) {
                    $foobar;
                    $barfoo;
                }
                PHP,
            TextEdit::create(19, 1, "\n    \n"),
            false,
            <<<'PHP'
                <?php

                if (false) {
                    
                    $foobar;
                    $barfoo;
                }
                PHP,
        ];

        yield 'delete line' => [
            <<<'PHP'
                <?php

                if (false) {
                    $foobar;
                    $barfoo;
                }
                PHP,
            TextEdit::create(20, 13, ''),
            false,
            <<<'PHP'
                <?php

                if (false) {
                    $barfoo;
                }
                PHP,
        ];

        yield 'newline' => [
            <<<'PHP'
                <?php
                $foobar;$barfoo;
                PHP,
            TextEdit::create(15, 0, "\n"),
            false,
        ];

        yield 'newline on newline' => [
            <<<'PHP'
                <?php

                $foobar;$barfoo;
                PHP,
            TextEdit::create(6, 0, "\n"),
            false,
            <<<'PHP'
                <?php


                $foobar;$barfoo;
                PHP,
        ];

        yield 'newline on newline after var' => [
            <<<'PHP'
                <?php
                $barfoo;

                $foobar;
                PHP,
            TextEdit::create(14, 0, "\n"),
            false,
            <<<'PHP'
                <?php
                $barfoo;


                $foobar;
                PHP,
        ];

        yield 'two newlines on newline after var' => [
            <<<'PHP'
                <?php
                $barfoo;

                $foobar;
                PHP,
            TextEdit::create(14, 1, "\n\n"),
            false,
            <<<'PHP'
                <?php
                $barfoo;


                $foobar;
                PHP,
        ];

        yield 'semicolon suffix with newline' => [
            <<<'PHP'
                <?php
                $foobar;$barfoo;
                PHP,
            TextEdit::create(14, 0, "\n"),
            false,
        ];

        yield 'semicolon' => [
            <<<'PHP'
                <?php
                $foobar;$barfoo;
                PHP,
            TextEdit::create(14, 0, ''),
            true,
        ];

        yield 'change token type' => [
            <<<'PHP'
                <?php
                $fooar;
                PHP,
            TextEdit::create(9, 0, ' $b'),
            false,
        ];

        yield 'method name' => [
            <<<'PHP'
                <?php
                self::fo
                PHP,
            TextEdit::create(14, 0, 'obar'),
            true,
            <<<'PHP'
                <?php
                self::foobar
                PHP,
        ];

        yield 'minus to arrow token should cause a reparse' => [
            <<<'PHP'
                <?php
                $foo-
                PHP,
            TextEdit::create(11, 0, '>'),
            false,
            <<<'PHP'
                <?php
                $foo->
                PHP,
        ];
    }
}
