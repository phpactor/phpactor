<?php

namespace Phpactor\WorseReflection\Tests\Unit\Bridge\TolerantParser\Incremental\Strategy;

use Generator;
use Phpactor\TextDocument\TextEdit;
use Phpactor\WorseReflection\Bridge\TolerantParser\Incremental\Strategy\CompoundNodeStrategy;
use Phpactor\WorseReflection\Bridge\TolerantParser\Incremental\UpdaterStrategy;
use Phpactor\WorseReflection\Tests\Unit\Bridge\TolerantParser\Incremental\UpdaterStrategyTestCase;

class CompoundNodeStrategyTest extends UpdaterStrategyTestCase
{
    /**
     * @return Generator<string,array{string,string}>
     */
    public static function provideUpdate(): Generator
    {
        yield 'compound: add new line' => [
            <<<'PHP'
                <?php
                function () {
                    $foobar;
                    $foobar;
                }
                PHP,
            TextEdit::create(32, 0, "\n" . '    $barf;'),
            true,
            <<<'PHP'
                <?php
                function () {
                    $foobar;
                    $barf;
                    $foobar;
                }
                PHP,
        ];

        yield 'compound: add first line' => [
            <<<'PHP'
                <?php
                function () {
                }
                PHP,
            TextEdit::create(19, 0, "\n" . '    $barf;'),
            true,
            <<<'PHP'
                <?php
                function () {
                    $barf;
                }
                PHP,
        ];

        yield 'compound: remove lines' => [
            <<<'PHP'
                <?php
                function () {
                    $barf;
                    $farf;
                }
                PHP,
            TextEdit::create(30, 11, ''),
            true,
            <<<'PHP'
                <?php
                function () {
                    $barf;
                }
                PHP,
        ];

        yield 'compound: remove line with prevous line' => [
            <<<'PHP'
                <?php
                function () {
                    $one;
                    $two;
                    $three;
                }
                PHP,
            TextEdit::create(31, 10, ''),
            true,
            <<<'PHP'
                <?php
                function () {
                    $one;
                    $three;
                }
                PHP,
        ];
    }

    public function strategy(): UpdaterStrategy
    {
        return new CompoundNodeStrategy();
    }
}
