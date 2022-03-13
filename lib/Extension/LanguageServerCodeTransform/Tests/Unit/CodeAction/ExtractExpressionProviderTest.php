<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Tests\Unit\CodeAction;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeTransform\Domain\Refactor\ExtractExpression;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\LanguageServerCodeTransform\CodeAction\ExtractExpressionProvider;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\ExtractExpressionCommand;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use function Amp\Promise\wait;

class ExtractExpressionProviderTest extends TestCase
{
    use ProphecyTrait;
    const EXAMPLE_SOURCE = 'foobar';
    const EXAMPLE_FILE = 'file:///somefile.php';

    /**
     * @var ObjectProphecy<ExtractExpression>
     */
    private ObjectProphecy $extractExpression;

    public function setUp(): void
    {
        $this->extractExpression = $this->prophesize(ExtractExpression::class);
    }

    /**
     * @dataProvider provideActionsData
     */
    public function testProvideActions(bool $shouldSucceed, array $expectedValue): void
    {
        $textDocumentItem = new TextDocumentItem(self::EXAMPLE_FILE, 'php', 1, self::EXAMPLE_SOURCE);
        $range = ProtocolFactory::range(0, 0, 0, 5);

        $this->extractExpression
            ->canExtractExpression(
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
                    'title' =>  'Extract expression',
                    'kind' => ExtractExpressionProvider::KIND,
                    'diagnostics' => [],
                    'command' => new Command(
                        'Extract method',
                        ExtractExpressionCommand::NAME,
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
    
    private function createProvider(): ExtractExpressionProvider
    {
        return new ExtractExpressionProvider($this->extractExpression->reveal());
    }
}
