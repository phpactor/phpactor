<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Tests\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use function Amp\Promise\wait;
use Generator;
use Phpactor\Extension\LanguageServerPhpstan\Model\PhpstanConfig;
use Phpactor\Extension\LanguageServerPhpstan\Model\PhpstanProcess;
use Phpactor\LanguageServerProtocol\DiagnosticSeverity;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Psr\Log\NullLogger;
use Phpactor\Extension\LanguageServerPhpstan\Tests\IntegrationTestCase;

class PhpstanProcessTest extends IntegrationTestCase
{
    /**
     * @param DiagnosticSeverity::* $configuredSeverity
     * @param array<Diagnostic> $expectedDiagnostics
     */
    #[DataProvider('provideLint')]
    public function testLint(string $source, int $configuredSeverity, array $expectedDiagnostics): void
    {
        $this->workspace()->reset();
        $this->workspace()->put('test.php', $source);
        $linter = new PhpstanProcess(
            $this->workspace()->path(),
            new PhpstanConfig(__DIR__ . '/../../../../../vendor/bin/phpstan', $configuredSeverity, '7', __DIR__ . '/../../../../../phpstan-baseline.neon', '200M'),
            new NullLogger()
        );
        $diagnostics = wait($linter->analyseInPlace($this->workspace()->path('test.php')));
        self::assertEquals($expectedDiagnostics, $diagnostics);
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideLint(): Generator
    {
        yield [
            '<?php $foobar = "string";',
            DiagnosticSeverity::ERROR,
            []
        ];

        yield [
            '<?php $foobar = $barfoo;',
            DiagnosticSeverity::ERROR,
            [
                new Diagnostic(
                    range: new Range(
                        new Position(0, 1),
                        new Position(0, 100)
                    ),
                    message: 'Variable $barfoo might not be defined.',
                    severity: DiagnosticSeverity::ERROR,
                    source: 'phpstan',
                    code: 'variable.undefined'
                )
            ]
        ];

        yield [
            '<?php $foobar = $barfoo;',
            DiagnosticSeverity::HINT,
            [
                new Diagnostic(
                    range: new Range(
                        new Position(0, 1),
                        new Position(0, 100)
                    ),
                    message: 'Variable $barfoo might not be defined.',
                    severity: DiagnosticSeverity::HINT,
                    source: 'phpstan',
                    code: 'variable.undefined'
                )
            ]
        ];
    }
}
