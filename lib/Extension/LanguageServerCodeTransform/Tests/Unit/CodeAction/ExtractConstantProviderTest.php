<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Tests\Unit\CodeAction;

use PHPUnit\Framework\Attributes\DataProvider;
use Amp\CancellationTokenSource;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeTransform\Domain\Refactor\ExtractConstant;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\LanguageServerCodeTransform\CodeAction\ExtractConstantProvider;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\ExtractConstantCommand;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use function Amp\Promise\wait;

class ExtractConstantProviderTest extends TestCase
{
    use ProphecyTrait;
    const EXAMPLE_SOURCE = 'foobar';
    const EXAMPLE_FILE = 'file:///somefile.php';

    /**
     * @var ObjectProphecy<ExtractConstant>
     */
    private ObjectProphecy $extractConstant;

    public function setUp(): void
    {
        $this->extractConstant = $this->prophesize(ExtractConstant::class);
    }

    #[DataProvider('provideActionsData')]
    public function testProvideActions(bool $shouldSucceed, array $expectedValue): void
    {
        $textDocumentItem = new TextDocumentItem(self::EXAMPLE_FILE, 'php', 1, self::EXAMPLE_SOURCE);
        $range = ProtocolFactory::range(0, 0, 0, 5);

        $this->extractConstant
            ->canExtractConstant(
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
                    'title' =>  'Extract constant',
                    'kind' => ExtractConstantProvider::KIND,
                    'diagnostics' => [],
                    'command' => new Command(
                        'Extract constant',
                        ExtractConstantCommand::NAME,
                        [
                            self::EXAMPLE_FILE,
                            0,
                            5
                        ]
                    )
                ])
            ]
        ];
    }

    private function createProvider(): ExtractConstantProvider
    {
        return new ExtractConstantProvider($this->extractConstant->reveal());
    }
}
