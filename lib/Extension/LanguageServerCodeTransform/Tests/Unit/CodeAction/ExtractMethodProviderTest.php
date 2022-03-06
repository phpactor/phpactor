<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Tests\Unit\CodeAction;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeTransform\Domain\Refactor\ExtractMethod;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\LanguageServerCodeTransform\CodeAction\ExtractMethodProvider;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\ExtractMethodCommand;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use function Amp\Promise\wait;
use Generator;

class ExtractMethodProviderTest extends TestCase
{
    use ProphecyTrait;

    const EXAMPLE_SOURCE = 'foobar';
    const EXAMPLE_FILE = 'file:///somefile.php';
    /**
     * @var ObjectProphecy
     */
    private $extractMethod;

    public function setUp(): void
    {
        $this->extractMethod = $this->prophesize(ExtractMethod::class);
    }
    /**
     * @dataProvider provideActionsData
     */
    public function testProvideActions(bool $shouldSucceed, array $expectedValue): void
    {
        $textDocumentItem = new TextDocumentItem(self::EXAMPLE_FILE, 'php', 1, self::EXAMPLE_SOURCE);
        $range = ProtocolFactory::range(0, 0, 0, 5);

        // @phpstan-ignore-next-line
        $this->extractMethod
            ->canExtractMethod(
                SourceCode::fromStringAndPath($textDocumentItem->text, $textDocumentItem->uri),
                $range->start->character,
                $range->end->character
            )
            ->willReturn($shouldSucceed)
            ->shouldBeCalled();

        $this->assertEquals(
            $expectedValue,
            wait($this->createProvider()->provideActionsFor(
                $textDocumentItem,
                $range
            ))
        );
    }

    public function provideActionsData(): Generator
    {
        yield 'Fail' => [
            false,
            []
        ];
        yield 'Success' => [
            true,
            [
                CodeAction::fromArray([
                    'title' =>  'Extract method',
                    'kind' => ExtractMethodProvider::KIND,
                    'diagnostics' => [],
                    'command' => new Command(
                        'Extract method',
                        ExtractMethodCommand::NAME,
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

    private function createProvider(): ExtractMethodProvider
    {
        // @phpstan-ignore-next-line
        return new ExtractMethodProvider($this->extractMethod->reveal());
    }
}
