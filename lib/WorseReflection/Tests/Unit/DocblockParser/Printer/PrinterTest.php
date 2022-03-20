<?php

namespace Phpactor\WorseReflection\Tests\Unit\DocblockParser\Printer;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\DocblockParser\Lexer;
use Phpactor\WorseReflection\DocblockParser\Parser;
use Phpactor\WorseReflection\DocblockParser\Printer\TestPrinter;

class PrinterTest extends TestCase
{
    /**
     * @dataProvider provideExamples
     */
    public function testPrint(string $path): void
    {
        $update = false;

        $contents = (string)file_get_contents($path);

        $parts = explode('---', $contents);

        if (empty($parts[0])) {
            $this->markTestIncomplete(sprintf('No example given for "%s"', $path));
            return;
        }

        $tokens = (new Lexer())->lex($parts[0]);
        $node = (new Parser())->parse($tokens);
        $rendered = (new TestPrinter())->print($node);

        /**
         * @phpstan-ignore-next-line
         */
        if (!isset($parts[1]) || $update) {
            file_put_contents($path, implode("---\n", [$parts[0], $rendered]));
            $this->markTestSkipped('Generated output');
            return;
        }

        self::assertEquals(trim($parts[1]), trim($rendered));
    }

    /**
     * @return Generator<mixed>
     */
    public function provideExamples(): Generator
    {
        foreach ((array)glob(__DIR__ . '/examples/*.test') as $path) {
            yield basename($path) => [
                $path
            ];
        }
    }
}
