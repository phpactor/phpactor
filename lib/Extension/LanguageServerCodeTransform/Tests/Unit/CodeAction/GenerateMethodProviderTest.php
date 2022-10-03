<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Tests\Unit\CodeAction;

use Amp\CancellationTokenSource;
use function Amp\Promise\wait;
use Generator;
use Phpactor\CodeTransform\Domain\Helper\MissingMethodFinder;
use Phpactor\CodeTransform\Domain\Helper\MissingMethodFinder\MissingMethod;
use Phpactor\Extension\LanguageServerCodeTransform\CodeAction\GenerateMethodProvider;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\GenerateMethodCommand;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServerProtocol\DiagnosticSeverity;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\TextDocument;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class GenerateMethodProviderTest extends TestCase
{
    use ProphecyTrait;
    public const EXAMPLE_SOURCE = 'foobar';
    public const EXAMPLE_FILE = 'file:///somefile.php';

    /**
     * @var ObjectProphecy<MissingMethodFinder>
     */
    private ObjectProphecy $finder;

    protected function setUp(): void
    {
        $this->finder = $this->prophesize(MissingMethodFinder::class);
    }

    /**
     * @dataProvider provideDiagnosticsTestData
     */
    public function testDiagnostics(array $missingMethods, array $expectedDiagnostics): void
    {
        $this->finder->find(Argument::type(TextDocument::class))->willReturn($missingMethods);
        $provider = $this->createProvider();

        $cancel = (new CancellationTokenSource())->getToken();
        self::assertEquals(
            $expectedDiagnostics,
            wait($provider->provideDiagnostics(
                new TextDocumentItem(self::EXAMPLE_FILE, 'php', 1, self::EXAMPLE_SOURCE),
                $cancel
            ))
        );
    }

    /**
     * @return Generator<string,array{array,array}|array{array<int,Phpactor\CodeTransform\Domain\Helper\MissingMethodFinder\MissingMethod>,array<int,Phpactor\LanguageServerProtocol\Diagnostic>}>
     */
    public function provideDiagnosticsTestData(): Generator
    {
        yield 'No missing methods' => [
            [],
            []
        ];

        yield 'Missing method' => [
            [
                new MissingMethod(self::EXAMPLE_SOURCE, ByteOffsetRange::fromInts(0, 5))
            ],
            [
                Diagnostic::fromArray([
                    'range' => ProtocolFactory::range(0, 0, 0, 5),
                    'message' => 'Method "foobar" does not exist',
                    'severity' => DiagnosticSeverity::WARNING,
                    'source' => 'phpactor',
                ])
            ]
        ];
    }

    /**
     * @dataProvider provideActionsTestData
     */
    public function testProvideActions(array $missingMethods, array $expectedActions): void
    {
        $this->finder->find(Argument::type(TextDocument::class))->willReturn($missingMethods);
        $provider = $this->createProvider();
        $cancel = (new CancellationTokenSource())->getToken();
        self::assertEquals(
            $expectedActions,
            wait($provider->provideActionsFor(
                new TextDocumentItem(self::EXAMPLE_FILE, 'php', 1, self::EXAMPLE_SOURCE),
                ProtocolFactory::range(0, 0, 0, 0),
                $cancel
            ))
        );
    }

    /**
     * @return Generator<string,array{array,array}|array{array<int,Phpactor\CodeTransform\Domain\Helper\MissingMethodFinder\MissingMethod>,array<int,Phpactor\LanguageServerProtocol\CodeAction>}>
     */
    public function provideActionsTestData(): Generator
    {
        yield 'No missing methods' => [
            [],
            []
        ];

        yield 'Missing method' => [
            [
                new MissingMethod(self::EXAMPLE_SOURCE, ByteOffsetRange::fromInts(0, 5))
            ],
            [
                CodeAction::fromArray([
                    'title' =>  'Fix "Method "foobar" does not exist"',
                    'kind' => GenerateMethodProvider::KIND,
                    'diagnostics' => [
                        Diagnostic::fromArray([
                            'range' => ProtocolFactory::range(0, 0, 0, 5),
                            'message' => 'Method "foobar" does not exist',
                            'severity' => DiagnosticSeverity::WARNING,
                            'source' => 'phpactor',
                        ])
                    ],
                    'command' => new Command(
                        'Generate method',
                        GenerateMethodCommand::NAME,
                        [
                            self::EXAMPLE_FILE,
                            0
                        ]
                    )
                ])
            ]
        ];
    }

    private function createProvider(): GenerateMethodProvider
    {
        return new GenerateMethodProvider($this->finder->reveal());
    }
}
