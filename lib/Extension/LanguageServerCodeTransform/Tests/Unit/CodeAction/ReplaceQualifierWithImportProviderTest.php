<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Tests\Unit\CodeAction;

use PHPUnit\Framework\Attributes\DataProvider;
use Amp\CancellationTokenSource;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeTransform\Domain\Refactor\ReplaceQualifierWithImport;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\LanguageServerCodeTransform\CodeAction\ReplaceQualifierWithImportProvider;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\ReplaceQualifierWithImportCommand;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use function Amp\Promise\wait;

class ReplaceQualifierWithImportProviderTest extends TestCase
{
    use ProphecyTrait;
    const EXAMPLE_SOURCE = 'foobar';
    const EXAMPLE_FILE = 'file:///somefile.php';

    /**
     * @var ObjectProphecy<ReplaceQualifierWithImport>
     */
    private ObjectProphecy $replaceQualifierWithImport;

    public function setUp(): void
    {
        $this->replaceQualifierWithImport = $this->prophesize(ReplaceQualifierWithImport::class);
    }

    #[DataProvider('provideActionsData')]
    public function testProvideActions(bool $shouldSucceed, array $expectedValue): void
    {
        $textDocumentItem = new TextDocumentItem(self::EXAMPLE_FILE, 'php', 1, self::EXAMPLE_SOURCE);
        $range = ProtocolFactory::range(0, 0, 0, 5);

        $this->replaceQualifierWithImport
            ->canReplaceWithImport(
                SourceCode::fromStringAndPath($textDocumentItem->text, $textDocumentItem->uri),
                $range->start->character,
            )
            ->willReturn($shouldSucceed)
            ->shouldBeCalled();

        $cancel = (new CancellationTokenSource())->getToken();
        $this->assertEquals(
            $expectedValue,
            wait($this->createProvider()->provideActionsFor(
                $textDocumentItem,
                $range,
                $cancel
            ))
        );
    }

    public static function provideActionsData(): Generator
    {
        yield 'Fail' => [
            false,
            []
        ];
        yield 'Success' => [
            true,
            [
                CodeAction::fromArray([
                    'title' => 'Replace qualifier with import',
                    'kind' => ReplaceQualifierWithImportProvider::KIND,
                    'diagnostics' => [],
                    'command' => new Command(
                        'Replace qualifier with import',
                        ReplaceQualifierWithImportCommand::NAME,
                        [
                            self::EXAMPLE_FILE,
                            0
                        ]
                    )
                ])
            ]
        ];
    }

    private function createProvider(): ReplaceQualifierWithImportProvider
    {
        return new ReplaceQualifierWithImportProvider($this->replaceQualifierWithImport->reveal());
    }
}
