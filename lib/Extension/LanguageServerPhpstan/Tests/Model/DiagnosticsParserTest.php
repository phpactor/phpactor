<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Tests\Model;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerPhpstan\Model\DiagnosticsParser;
use RuntimeException;

class DiagnosticsParserTest extends TestCase
{
    /**
     * @dataProvider provideParse
     */
    public function testParse(string $phpstanJson, int $count): void
    {
        self::assertCount($count, (new DiagnosticsParser())->parse($phpstanJson));
    }

    /**
     * @return Generator<array{string,int}>
     */
    public function provideParse(): Generator
    {
        yield [
            '{"totals":{"errors":0,"file_errors":1},"files":{"/home/daniel/www/phpactor/language-server-phpstan/test.php":{"errors":1,"messages":[{"message":"Undefined variable: $bar","line":3,"ignorable":true}]}},"errors":[]}',
            1
        ];
    }

    public function testExceptionOnNonJsonString(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('stdout was not JSON');
        (new DiagnosticsParser())->parse('stdout was not JSON');
    }
}
