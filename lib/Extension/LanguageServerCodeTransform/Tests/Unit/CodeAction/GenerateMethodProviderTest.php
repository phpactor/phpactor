<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Tests\Unit\CodeAction;

use PHPUnit\Framework\Attributes\DataProvider;
use Amp\CancellationTokenSource;
use Amp\Success;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeTransform\Domain\Helper\MissingMemberFinder;
use Phpactor\CodeTransform\Domain\Helper\MissingMemberFinder\MissingMember;
use Phpactor\Extension\LanguageServerCodeTransform\CodeAction\GenerateMemberProvider;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\GenerateMemberCommand;
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
     * @var ObjectProphecy<MissingMemberFinder>
     */
    private ObjectProphecy $finder;

    protected function setUp(): void
    {
        $this->finder = $this->prophesize(MissingMemberFinder::class);
    }

    #[DataProvider('provideDiagnosticsTestData')]
    public function testDiagnostics(array $missingMethods, array $expectedDiagnostics): void
    {
        $this->finder->find(Argument::type(TextDocument::class))->willReturn(new Success($missingMethods));
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
     * @return Generator<string, (array{array, array} | array{array<int, Phpactor\CodeTransform\Domain\Helper\MissingMethodFinder\MissingMethod>, array<int, Diagnostic>})>
     */
    public static function provideDiagnosticsTestData(): Generator
    {
        yield 'No missing methods' => [
            [],
            []
        ];

        yield 'Missing method' => [
            [
                new MissingMember(self::EXAMPLE_SOURCE, ByteOffsetRange::fromInts(0, 5), 'method')
            ],
            [
                new Diagnostic(
                    range: ProtocolFactory::range(0, 0, 0, 5),
                    message: 'Method "foobar" does not exist',
                    severity: DiagnosticSeverity::WARNING,
                    source: 'phpactor',
                )
            ]
        ];
    }

    #[DataProvider('provideActionsTestData')]
    public function testProvideActions(array $missingMethods, array $expectedActions): void
    {
        $this->finder->find(Argument::type(TextDocument::class))->willReturn(new Success($missingMethods));
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
     * @return Generator<string, (array{array, array} | array{array<int, Phpactor\CodeTransform\Domain\Helper\MissingMethodFinder\MissingMethod>, array<int, CodeAction>})>
     */
    public static function provideActionsTestData(): Generator
    {
        yield 'No missing methods' => [
            [],
            []
        ];

        yield 'Missing method' => [
            [
                new MissingMember(self::EXAMPLE_SOURCE, ByteOffsetRange::fromInts(0, 5), 'method')
            ],
            [
                CodeAction::fromArray([
                    'title' =>  'Fix "Method "foobar" does not exist"',
                    'kind' => GenerateMemberProvider::KIND,
                    'diagnostics' => [
                        new Diagnostic(
                            range: ProtocolFactory::range(0, 0, 0, 5),
                            message: 'Method "foobar" does not exist',
                            severity: DiagnosticSeverity::WARNING,
                            source: 'phpactor',
                        )
                    ],
                    'command' => new Command(
                        'Generate member',
                        GenerateMemberCommand::NAME,
                        [
                            self::EXAMPLE_FILE,
                            0
                        ]
                    )
                ])
            ]
        ];
    }

    private function createProvider(): GenerateMemberProvider
    {
        return new GenerateMemberProvider($this->finder->reveal());
    }
}
