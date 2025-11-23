<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Tests\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerPhpstan\Model\DiagnosticsParser;
use Phpactor\LanguageServerProtocol\CodeDescription;
use Phpactor\LanguageServerProtocol\DiagnosticSeverity;
use RuntimeException;

class DiagnosticsParserTest extends TestCase
{
    #[DataProvider('provideParse')]
    public function testParse(string $phpstanJson, int $count): void
    {
        self::assertCount($count, (new DiagnosticsParser())->parse($phpstanJson, DiagnosticSeverity::ERROR));
    }

    /**
     * @return Generator<array{string,int}>
     */
    public static function provideParse(): Generator
    {
        yield [
            '{"totals":{"errors":0,"file_errors":1},"files":{"/home/daniel/www/phpactor/language-server-phpstan/test.php":{"errors":1,"messages":[{"message":"Undefined variable: $bar","line":3,"ignorable":true}]}},"errors":[]}',
            1
        ];
        yield [
            <<<'EOT'
                {
                  "totals": {
                    "errors": 0,
                    "file_errors": 6
                  },
                  "files": {
                    "/home/daniel/www/php-tui/cli-parser/src/Type/TypeFactory.php": {
                      "errors": 1,
                      "messages": [
                        {
                          "message": "Method PhpTui\\CliParser\\Type\\TypeFactory::fromReflectionType() should return PhpTui\\CliParser\\Type\\Type<mixed> but returns PhpTui\\CliParser\\Type\\BooleanType|PhpTui\\CliParser\\Type\\FloatType|PhpTui\\CliParser\\Type\\IntegerType|PhpTui\\CliParser\\Type\\StringType.",
                          "line": 37,
                          "ignorable": true,
                          "tip": "• Template type TParseType on class PhpTui\\CliParser\\Type\\Type is not covariant. Learn more: https://phpstan.org/blog/whats-up-with-template-covariant\n• Template type TParseType on class PhpTui\\CliParser\\Type\\Type is not covariant. Learn more: https://phpstan.org/blog/whats-up-with-template-covariant\n• Template type TParseType on class PhpTui\\CliParser\\Type\\Type is not covariant. Learn more: https://phpstan.org/blog/whats-up-with-template-covariant\n• Template type TParseType on class PhpTui\\CliParser\\Type\\Type is not covariant. Learn more: https://phpstan.org/blog/whats-up-with-template-covariant"
                        }
                      ]
                    },
                    "/home/daniel/www/php-tui/cli-parser/tests/Unit/ParserTest.php": {
                      "errors": 3,
                      "messages": [
                        {
                          "message": "Syntax error, unexpected ';', expecting ',' or ']' or ')' on line 64",
                          "line": 64,
                          "ignorable": false
                        },
                        {
                          "message": "Syntax error, unexpected ';' on line 70",
                          "line": 70,
                          "ignorable": false
                        },
                        {
                          "message": "Syntax error, unexpected '}', expecting EOF on line 129",
                          "line": 129,
                          "ignorable": false
                        }
                      ]
                    },
                    "/home/daniel/www/php-tui/cli-parser/tests/Unit/Type/TypeFactoryTest.php": {
                      "errors": 2,
                      "messages": [
                        {
                          "message": "Parameter #1 ...$types of class PhpTui\\CliParser\\Type\\UnionType constructor expects PhpTui\\CliParser\\Type\\Type<mixed>, PhpTui\\CliParser\\Type\\StringType given.",
                          "line": 73,
                          "ignorable": true,
                          "tip": "Template type TParseType on class PhpTui\\CliParser\\Type\\Type is not covariant. Learn more: https://phpstan.org/blog/whats-up-with-template-covariant"
                        },
                        {
                          "message": "Parameter #2 ...$types of class PhpTui\\CliParser\\Type\\UnionType constructor expects PhpTui\\CliParser\\Type\\Type<mixed>, PhpTui\\CliParser\\Type\\IntegerType given.",
                          "line": 73,
                          "ignorable": true,
                          "tip": "Template type TParseType on class PhpTui\\CliParser\\Type\\Type is not covariant. Learn more: https://phpstan.org/blog/whats-up-with-template-covariant"
                        }
                      ]
                    }
                  },
                  "errors": []
                }
                EOT,
            9
        ];
    }


    public function testTipUrl(): void
    {
        $diagnostics =  (new DiagnosticsParser())->parse(
            json_encode([
                'files' => [
                    'file1.php' => [
                        'messages' => [
                            [
                                'line' => 2,
                                'message' => 'foobar',
                                'tip' => 'Template is not covariant. Learn more: https://phpstan.org/blog/whats-up-with-template-covariant'

                            ]
                        ]
                    ]
                ],
            ], JSON_THROW_ON_ERROR),
            DiagnosticSeverity::ERROR
        );
        self::assertCount(2, $diagnostics);
        $diagnostic = $diagnostics[1];
        self::assertEquals(
            new CodeDescription('https://phpstan.org/blog/whats-up-with-template-covariant'),
            $diagnostic->codeDescription
        );
    }

    public function testExceptionOnNonJsonString(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('stdout was not JSON');
        (new DiagnosticsParser())->parse('stdout was not JSON', DiagnosticSeverity::ERROR);
    }
}
