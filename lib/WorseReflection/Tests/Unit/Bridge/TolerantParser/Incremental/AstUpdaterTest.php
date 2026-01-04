<?php

namespace Phpactor\WorseReflection\Tests\Unit\Bridge\TolerantParser\Incremental;

use Generator;
use Microsoft\PhpParser\Parser;
use PHPUnit\Framework\Attributes\DataProvider;
use Phpactor\ConfigLoader\Tests\TestCase;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Bridge\TolerantParser\Incremental\AstUpdater;

class AstUpdaterTest extends TestCase
{
    #[DataProvider('provideEditInToken')]
    #[DataProvider('provideEditInCompoundStatementList')]
    public function testUpdate(string $source, TextEdit $textEdit, bool $expectedSuccess, ?string $sanityCheck = null): void
    {
        $updatedSource = TextEdits::one($textEdit)->apply($source);

        $uri = 'file:///foo';
        $ast = (new Parser())->parseSourceFile($source, $uri);
        $incrementalAstResult = (new AstUpdater($ast))->apply($textEdit, TextDocumentUri::fromString($uri));
        $freshAst = (new Parser())->parseSourceFile($updatedSource, $uri);

        if ($sanityCheck !== null) {
            self::assertEquals($sanityCheck, $updatedSource);
        }

        self::assertEquals($updatedSource, $incrementalAstResult->ast->fileContents);
        self::assertEquals($freshAst, $incrementalAstResult->ast, 'Incrementally updated AST is the same as fresh AST');

        if ($expectedSuccess) {
            self::assertNull($incrementalAstResult->failureReason);
            self::assertTrue($incrementalAstResult->success);
            return;
        }

        self::assertFalse($incrementalAstResult->success, 'Expect to fail');
    }

    /**
     * @return Generator<string,array{string,string}>
     */
    public static function provideEditInToken(): Generator
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
            true,
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
            true,
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

    /**
     * @return Generator<string,array{string,string}>
     */
    public static function provideEditInCompoundStatementList(): Generator
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
}
