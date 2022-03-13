<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Tests\Unit\CodeAction;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeTransform\Domain\Helper\MissingMethodFinder;
use Phpactor\CodeTransform\Domain\Helper\MissingMethodFinder\MissingMethod;
use Phpactor\Extension\LanguageServerCodeTransform\CodeAction\GenerateMethodProvider;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\GenerateMethodCommand;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServerProtocol\DiagnosticSeverity;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\TextDocument;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use function Amp\Promise\wait;

class GenerateMethodProviderTest extends TestCase
{
    use ProphecyTrait;
    const EXAMPLE_SOURCE = 'foobar';
    const EXAMPLE_FILE = 'file:///somefile.php';

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

        self::assertEquals(
            $expectedDiagnostics,
            wait($provider->provideDiagnostics(
                new TextDocumentItem(self::EXAMPLE_FILE, 'php', 1, self::EXAMPLE_SOURCE)
            ))
        );
    }

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
        self::assertEquals(
            $expectedActions,
            wait($provider->provideActionsFor(
                new TextDocumentItem(self::EXAMPLE_FILE, 'php', 1, self::EXAMPLE_SOURCE),
                ProtocolFactory::range(0, 0, 0, 0)
            ))
        );
    }

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
