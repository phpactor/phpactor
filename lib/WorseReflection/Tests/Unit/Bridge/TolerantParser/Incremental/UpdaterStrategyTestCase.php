<?php

namespace Phpactor\WorseReflection\Tests\Unit\Bridge\TolerantParser\Incremental;

use PHPUnit\Framework\TestCase;
use Generator;
use Microsoft\PhpParser\Parser;
use PHPUnit\Framework\Attributes\DataProvider;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider;
use Phpactor\WorseReflection\Bridge\TolerantParser\Incremental\AstUpdater;
use Phpactor\WorseReflection\Bridge\TolerantParser\Incremental\UpdaterStrategy;

abstract class UpdaterStrategyTestCase extends TestCase
{
    abstract public function strategy(): UpdaterStrategy;
    abstract public static function provideUpdate(): Generator;

    /**
     * @return Generator<string,array{string,string}>
     */
    #[DataProvider('provideUpdate')]
    public function testUpdate(
        string $source,
        TextEdit $textEdit,
        bool $expectedSuccess,
        ?string $sanityCheck = null
    ): void {
        $updatedSource = TextEdits::one($textEdit)->apply($source);

        $uri = 'file:///foo';
        $ast = (new Parser())->parseSourceFile($source, $uri);
        $incrementalAstResult = (new AstUpdater(
            $ast,
            new TolerantAstProvider(),
            [$this->strategy()]
        ))->apply($textEdit, TextDocumentUri::fromString($uri));
        $freshAst = (new Parser())->parseSourceFile($updatedSource, $uri);

        if ($sanityCheck !== null) {
            self::assertEquals($sanityCheck, $updatedSource);
        }

        self::assertEquals($updatedSource, $incrementalAstResult->ast->fileContents);
        self::assertEquals($freshAst, $incrementalAstResult->ast, 'Incrementally updated AST is the same as fresh AST');

        self::assertSame($expectedSuccess, $incrementalAstResult->success, 'Expect to fail');
        //self::assertNull($incrementalAstResult->reason);
    }
}
