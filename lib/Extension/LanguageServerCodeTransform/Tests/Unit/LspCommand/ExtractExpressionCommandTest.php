<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Tests\Unit\LspCommand;

use Exception;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeTransform\Domain\Exception\TransformException;
use Phpactor\CodeTransform\Domain\Refactor\ExtractExpression;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\LanguageServerBridge\Converter\TextEditConverter;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\ExtractExpressionCommand;
use Phpactor\LanguageServerProtocol\ApplyWorkspaceEditResult;
use Phpactor\LanguageServerProtocol\MessageType;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\LanguageServer\LanguageServerTesterBuilder;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ExtractExpressionCommandTest extends TestCase
{
    use ProphecyTrait;
    const EXAMPLE_SOURCE = '<?php ';
    const EXAMPLE_URI = 'file:///file.php';
    const EXAMPLE_OFFSET = 5;

    public function testSuccessfulCall(): void
    {
        $textEdits = new TextEdits(TextEdit::create(self::EXAMPLE_OFFSET, 1, 'test'));

        $extractExpression = $this->prophesize(ExtractExpression::class);
        $extractExpression->extractExpression(Argument::type(SourceCode::class), 0, self::EXAMPLE_OFFSET, ExtractExpressionCommand::DEFAULT_VARIABLE_NAME)
            ->shouldBeCalled()
            ->willReturn($textEdits);

        [$tester, $builder] = $this->createTester($extractExpression);
        $tester->workspace()->executeCommand('extract_expression', [self::EXAMPLE_URI, 0, self::EXAMPLE_OFFSET]);
        $builder->responseWatcher()->resolveLastResponse(new ApplyWorkspaceEditResult(true));

        $applyEdit = $builder->transmitter()->filterByMethod('workspace/applyEdit')->shiftRequest();

        self::assertNotNull($applyEdit);
        self::assertEquals([
            'edit' => new WorkspaceEdit([
                self::EXAMPLE_URI => TextEditConverter::toLspTextEdits(
                    $textEdits,
                    self::EXAMPLE_SOURCE
                )
            ]),
            'label' => 'Extract expression'
        ], $applyEdit->params);
    }

    /**
     * @dataProvider provideExceptions
     */
    public function testFailedCall(Exception $exception): void
    {
        $extractExpression = $this->prophesize(ExtractExpression::class);
        $extractExpression->extractExpression(Argument::type(SourceCode::class), 0, self::EXAMPLE_OFFSET, ExtractExpressionCommand::DEFAULT_VARIABLE_NAME)
             ->shouldBeCalled()
             ->willThrow($exception);

        [$tester, $builder] = $this->createTester($extractExpression);
        $tester->workspace()->executeCommand('extract_expression', [self::EXAMPLE_URI, 0, self::EXAMPLE_OFFSET]);
        $showMessage = $builder->transmitter()->filterByMethod('window/showMessage')->shiftNotification();

        self::assertNotNull($showMessage);
        self::assertEquals([
             'type' => MessageType::WARNING,
             'message' => $exception->getMessage()
         ], $showMessage->params);
    }

    /**
     * @return Generator<class-string, array{Exception}>
     */
    public function provideExceptions(): Generator
    {
        yield TransformException::class => [ new TransformException('Error message!') ];
    }

    /**
     * @param ObjectProphecy<ExtractExpression> $extractExpression
     */
    private function createTester(ObjectProphecy $extractExpression): array
    {
        $builder = LanguageServerTesterBuilder::createBare()
             ->enableTextDocuments()
             ->enableCommands();
        $builder->addCommand('extract_expression', new ExtractExpressionCommand(
            $builder->clientApi(),
            $builder->workspace(),
            $extractExpression->reveal()
        ));

        $tester = $builder->build();
        $tester->textDocument()->open(self::EXAMPLE_URI, self::EXAMPLE_SOURCE);

        return [$tester, $builder];
    }
}
