<?php

namespace Phpactor\Extension\LanguageServerPsalm\Tests\Model;

use Closure;
use Generator;
use Phpactor\Extension\LanguageServerPsalm\Model\PsalmConfig;
use Phpactor\Extension\LanguageServerPsalm\Model\PsalmProcess;
use Phpactor\LanguageServerProtocol\DiagnosticSeverity;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Psr\Log\NullLogger;
use Phpactor\Extension\LanguageServerPsalm\Tests\IntegrationTestCase;
use Symfony\Component\Process\Process;
use function Amp\Promise\wait;

/**
 * @group slow
 */
class PsalmProcessTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    /**
     * @dataProvider provideLint
     * @param Closure(list<Diagnostic>):void $assertion
     */
    public function testLint(string $source, Closure $assertion, int $initLevel = 1, bool $shouldShowInfo = true, int $errorLevel = null): void
    {
        $psalmBin = __DIR__ . '/../../../../../vendor/bin/psalm';
        $this->workspace()->reset();

        // without a src dir, psalm crashes
        $this->workspace()->mkdir('src');

        $this->workspace()->put(
            'composer.json',
            <<<'EOT'
                {
                    "name": "test/project",
                    "autoload": {
                        "psr-4": {
                            "Phpactor\\Extension\\LanguageServerPsalm\\": "/"
                        }
                    }
                }
                EOT
        );
        (Process::fromShellCommandline('composer install', $this->workspace()->path()))->mustRun();

        (new Process([$psalmBin, '--init', 'src', $initLevel], $this->workspace()->path()))->mustRun();
        (new Process([$psalmBin, '--clear-cache'], $this->workspace()->path()))->mustRun();
        $this->workspace()->put('src/test.php', $source);
        $linter = new PsalmProcess(
            $this->workspace()->path(),
            new PsalmConfig(
                phpstanBin: $psalmBin,
                shouldShowInfo: $shouldShowInfo,
                useCache: false,
                errorLevel: $errorLevel,
            ),
            new NullLogger()
        );

        $diagnostics = wait($linter->analyse($this->workspace()->path('src/test.php')));
        $assertion($diagnostics);

    }

    /**
     * @return Generator<mixed>
     */
    public function provideLint(): Generator
    {
        yield [
            '<?php $foobar = "string"; echo $foobar;',
            function (array $diagnostics): void {
                self::assertCount(0, $diagnostics);
            }
        ];

        yield [
            '<?php $foobar = $barfoo;',
            function (array $diagnostics): void {
                self::assertDiagnostics(
                    [
                        Diagnostic::fromArray([
                            'range' => new Range(
                                new Position(0, 5),
                                new Position(0, 12)
                            ),
                            'message' => 'Unable to determine the type that $foobar is being assigned to',
                            'severity' => DiagnosticSeverity::ERROR,
                            'source' => 'psalm'
                        ]),
                        Diagnostic::fromArray([
                            'range' => new Range(
                                new Position(0, 5),
                                new Position(0, 12)
                            ),
                            'message' => '$foobar is never referenced or the value is not used',
                            'severity' => DiagnosticSeverity::ERROR,
                            'source' => 'psalm'
                        ]),
                        Diagnostic::fromArray([
                            'range' => new Range(
                                new Position(0, 15),
                                new Position(0, 22)
                            ),
                            'message' => 'Cannot find referenced variable $barfoo in global scope',
                            'severity' => DiagnosticSeverity::ERROR,
                            'source' => 'psalm'
                        ])
                    ],
                    $diagnostics
                );
            }
        ];

        yield [
            '<?php $foobar = $barfoo;',
            function (array $diagnostics): void {
                self::assertDiagnostics(
                    [
                        Diagnostic::fromArray([
                            'range' => new Range(
                                new Position(0, 5),
                                new Position(0, 12)
                            ),
                            'message' => 'Unable to determine the type that $foobar is being assigned to',
                            'severity' => DiagnosticSeverity::WARNING,
                            'source' => 'psalm'
                        ]),
                        Diagnostic::fromArray([
                            'range' => new Range(
                                new Position(0, 15),
                                new Position(0, 22)
                            ),
                            'message' => 'Cannot find referenced variable $barfoo in global scope',
                            'severity' => DiagnosticSeverity::ERROR,
                            'source' => 'psalm'
                        ])
                    ],
                    $diagnostics
                );
            },
            2,
            true
        ];

        yield 'should not show info' => [
            '<?php $foobar = $barfoo;',
            function (array $diagnostics): void {
                self::assertDiagnostics(
                    [
                        Diagnostic::fromArray([
                            'range' => new Range(
                                new Position(0, 15),
                                new Position(0, 22)
                            ),
                            'message' => 'Cannot find referenced variable $barfoo in global scope',
                            'severity' => DiagnosticSeverity::ERROR,
                            'source' => 'psalm'
                        ])
                    ],
                    $diagnostics
                );
            },
            2,
            false
        ];

        yield 'do not override error level' => [
            '<?php function foo($bar) { $bar->foo(); } ',
            function (array $diagnostics): void {
                self::assertCount(0, $diagnostics);
            },
            7,
            false,
        ];

        yield 'override error level' => [
            '<?php function foo($bar) { $bar->foo(); } ',
            function (array $diagnostics): void {
                self::assertCount(3, $diagnostics);
            },
            7,
            false,
            1,
        ];
    }
    private static function assertDiagnostics(array $expectedDiagnostics, $diagnostics) {
        usort($diagnostics, fn (Diagnostic $a, Diagnostic $b) => strcasecmp($a->message, $b->message));
        usort($expectedDiagnostics, fn (Diagnostic $a, Diagnostic $b) => strcasecmp($a->message, $b->message));
        self::assertEquals($expectedDiagnostics, $diagnostics);
    }
}
