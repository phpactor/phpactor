<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Tests\Unit\LspCommand;

use PHPUnit\Framework\Attributes\DataProvider;
use Exception;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeTransform\Domain\Exception\TransformException;
use Phpactor\CodeTransform\Domain\Refactor\ExtractMethod;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\LanguageServerBridge\Converter\TextEditConverter;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\ExtractMethodCommand;
use Phpactor\LanguageServerProtocol\ApplyWorkspaceEditResult;
use Phpactor\LanguageServerProtocol\MessageType;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\LanguageServer\LanguageServerTesterBuilder;
use Phpactor\TextDocument\TextDocumentEdits;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\TextEdits;
use Phpactor\TextDocument\TextEdit;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ExtractMethodCommandTest extends TestCase
{
    use ProphecyTrait;
    const EXAMPLE_SOURCE = '<?php ';
    const EXAMPLE_URI = 'file:///file.php';
    const EXAMPLE_OFFSET = 5;

    public function testSuccessfulCall(): void
    {
        $textEdits = new TextDocumentEdits(
            TextDocumentUri::fromString(self::EXAMPLE_URI),
            new TextEdits(TextEdit::create(self::EXAMPLE_OFFSET, 1, 'test'))
        );

        $extractMethod = $this->prophesize(ExtractMethod::class);
        $extractMethod->extractMethod(Argument::type(SourceCode::class), 0, self::EXAMPLE_OFFSET, ExtractMethodCommand::DEFAULT_METHOD_NAME)
            ->shouldBeCalled()
            ->willReturn($textEdits);

        [$tester, $builder] = $this->createTester($extractMethod);
        $tester->workspace()->executeCommand('extract_method', [self::EXAMPLE_URI, 0, self::EXAMPLE_OFFSET]);
        $builder->responseWatcher()->resolveLastResponse(new ApplyWorkspaceEditResult(true));

        $applyEdit = $builder->transmitter()->filterByMethod('workspace/applyEdit')->shiftRequest();

        self::assertNotNull($applyEdit);
        self::assertEquals([
            'edit' => new WorkspaceEdit([
                (string)$textEdits->uri() => TextEditConverter::toLspTextEdits(
                    $textEdits->textEdits(),
                    self::EXAMPLE_SOURCE
                )
            ]),
            'label' => 'Extract method'
        ], $applyEdit->params);
    }

    #[DataProvider('provideExceptions')]
    public function testFailedCall(Exception $exception): void
    {
        $extractMethod = $this->prophesize(ExtractMethod::class);
        $extractMethod->extractMethod(Argument::type(SourceCode::class), 0, self::EXAMPLE_OFFSET, ExtractMethodCommand::DEFAULT_METHOD_NAME)
             ->shouldBeCalled()
             ->willThrow($exception);

        [$tester, $builder] = $this->createTester($extractMethod);
        $tester->workspace()->executeCommand('extract_method', [self::EXAMPLE_URI, 0, self::EXAMPLE_OFFSET]);
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
    public static function provideExceptions(): Generator
    {
        yield TransformException::class => [ new TransformException('Error message!') ];
    }


    private function createTester(ObjectProphecy $extractMethod): array
    {
        $builder = LanguageServerTesterBuilder::createBare()
             ->enableTextDocuments()
             ->enableCommands();
        $builder->addCommand('extract_method', new ExtractMethodCommand(
            $builder->clientApi(),
            $builder->workspace(),
            $extractMethod->reveal()
        ));

        $tester = $builder->build();
        $tester->textDocument()->open(self::EXAMPLE_URI, self::EXAMPLE_SOURCE);

        return [$tester, $builder];
    }
}
