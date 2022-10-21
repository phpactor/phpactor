<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Tests\Unit\CodeAction;

use Amp\CancellationTokenSource;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeTransform\Domain\Refactor\SimplifyClassName;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\LanguageServerCodeTransform\CodeAction\SimplifyClassNameProvider;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\SimplifyClassNameCommand;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use function Amp\Promise\wait;

class SimplifyClassNameProviderTest extends TestCase
{
    use ProphecyTrait;
    const EXAMPLE_SOURCE = 'foobar';
    const EXAMPLE_FILE = 'file:///somefile.php';

    /**
     * @var ObjectProphecy<SimplifyClassName>
     */
    private ObjectProphecy $simplifyClassName;

    public function setUp(): void
    {
        $this->simplifyClassName = $this->prophesize(SimplifyClassName::class);
    }

    /**
     * @dataProvider provideActionsData
     */
    public function testProvideActions(bool $shouldSucceed, array $expectedValue): void
    {
        $textDocumentItem = new TextDocumentItem(self::EXAMPLE_FILE, 'php', 1, self::EXAMPLE_SOURCE);
        $range = ProtocolFactory::range(0, 0, 0, 5);

        $this->simplifyClassName
            ->canSimplifyClassNameName(
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
                    'title' => 'Expand class name',
                    'kind' => SimplifyClassNameProvider::KIND,
                    'diagnostics' => [],
                    'command' => new Command(
                        'Expand class',
                        SimplifyClassNameCommand::NAME,
                        [
                            self::EXAMPLE_FILE,
                            0
                        ]
                    )
                ])
            ]
        ];
    }

    private function createProvider(): SimplifyClassNameProvider
    {
        return new SimplifyClassNameProvider($this->simplifyClassName->reveal());
    }
}
