<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Refactor;

use Generator;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseExtractConstant;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Domain\Exception\TransformException;
use Phpactor\TestUtils\ExtractOffset;

class WorseExtractConstantTest extends WorseTestCase
{
    /**
     * @dataProvider provideExtractMethod
     */
    public function testExtractConstant(string $test, string $name): void
    {
        [$source, $expected, $offset] = $this->sourceExpectedAndOffset(__DIR__ . '/fixtures/' . $test);

        $extractConstant = new WorseExtractConstant($this->reflectorForWorkspace($source), $this->updater());
        $textDocumentEdits = $extractConstant->extractConstant(SourceCode::fromStringAndPath($source, 'file:///source'), $offset, $name);
        $sourceCode = SourceCode::fromStringAndPath($source, 'file:///source');
        $transformed = SourceCode::fromStringAndPath(
            (string) $textDocumentEdits->textEdits()->apply($sourceCode),
            $textDocumentEdits->uri()->path()
        );

        $this->assertEquals(trim($expected), trim($transformed));
    }
    /**
     * @return Generator<string,array<int,string>>
     */
    public function provideExtractMethod(): Generator
    {
        yield 'string' => [ 'extractConstant1.test', 'HELLO_WORLD' ];
        yield 'numeric' => [ 'extractConstant2.test', 'HELLO_WORLD' ];
        yield 'array_key' => [ 'extractConstant3.test', 'HELLO_WORLD' ];
        yield 'namespaced' => [ 'extractConstant4.test', 'HELLO_WORLD' ];
        yield 'replace all' => [ 'extractConstant5.test', 'HELLO_WORLD' ];
        yield 'replace all numeric' => [ 'extractConstant6.test', 'HOUR' ];
        yield 'replace heredoc' => [ 'extractConstant7.test', 'HELLO_WORLD' ];
    }

    public function testNoClass(): void
    {
        $this->expectException(TransformException::class);
        $this->expectExceptionMessage('Node does not belong to a class');

        $code = <<<'EOT'
            <?php 1234;
            EOT
        ;

        $extractConstant = new WorseExtractConstant($this->reflectorForWorkspace($code), $this->updater());
        $extractConstant->extractConstant(SourceCode::fromString($code), 8, 'asd');
    }

    public function testNoOverwritingOfExistingConstants(): void
    {
        $this->expectException(TransformException::class);
        $this->expectExceptionMessage('Constant with name TEXT already exists on class Test');

        $code = <<<'EOT'
            <?php
            class Test {
                const TEXT = 'Cool constant';

                public function doSomething(): void
                {
                    echo 'SomeT<>ext';
                }
            }
            EOT;

        [$source, $offset] = ExtractOffset::fromSource($code);
        $extractConstant = new WorseExtractConstant($this->reflectorForWorkspace($source), $this->updater());
        $extractConstant->extractConstant(SourceCode::fromString($source), $offset, 'TEXT');
    }
}
