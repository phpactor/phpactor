<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Tests\Unit\LspCommand;

use Phpactor\LanguageServerProtocol\ApplyWorkspaceEditResult;
use Phpactor\LanguageServerProtocol\MessageType;
use Phpactor\LanguageServer\LanguageServerTesterBuilder;
use Phpactor\LanguageServer\Test\LanguageServerTester;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\TextDocument\TextDocumentLocator\InMemoryDocumentLocator;
use Phpactor\WorseReflection\Core\Exception\CouldNotResolveNode;
use Phpactor\CodeTransform\Domain\Exception\TransformException;
use Phpactor\WorseReflection\Core\Exception\MethodCallNotFound;
use Phpactor\CodeTransform\Domain\Refactor\GenerateMember;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\LanguageServerBridge\Converter\TextEditConverter;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\GenerateMemberCommand;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\TextDocument\TextDocumentEdits;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Exception;
use Prophecy\Prophecy\ObjectProphecy;

class GenerateMethodCommandTest extends TestCase
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

        $generateMethod = $this->prophesize(GenerateMember::class);
        $generateMethod->generateMember(Argument::type(SourceCode::class), self::EXAMPLE_OFFSET)
            ->shouldBeCalled()
            ->willReturn($textEdits);

        [$tester, $builder] = $this->createTester($generateMethod);
        $tester->workspace()->executeCommand('generate', [self::EXAMPLE_URI, self::EXAMPLE_OFFSET]);
        $builder->responseWatcher()->resolveLastResponse(new ApplyWorkspaceEditResult(true));

        $applyEdit = $builder->transmitter()->filterByMethod('workspace/applyEdit')->shiftRequest();

        self::assertNotNull($applyEdit);
        self::assertEquals([
            'edit' => new WorkspaceEdit([
                $textEdits->uri()->__toString() => TextEditConverter::toLspTextEdits(
                    $textEdits->textEdits(),
                    self::EXAMPLE_SOURCE
                )
            ]),
            'label' => 'Generate method'
        ], $applyEdit->params);
    }

    /**
     * @dataProvider provideExceptions
     */
    public function testFailedCall(Exception $exception): void
    {
        $generateMethod = $this->prophesize(GenerateMember::class);
        $generateMethod->generateMember(Argument::type(SourceCode::class), self::EXAMPLE_OFFSET)
            ->shouldBeCalled()
            ->willThrow($exception);

        [$tester, $builder] = $this->createTester($generateMethod);
        $tester->workspace()->executeCommand('generate', [self::EXAMPLE_URI, self::EXAMPLE_OFFSET]);
        $showMessage = $builder->transmitter()->filterByMethod('window/showMessage')->shiftNotification();

        self::assertNotNull($showMessage);
        self::assertEquals([
            'type' => MessageType::WARNING,
            'message' => $exception->getMessage()
        ], $showMessage->params);
    }

    public function provideExceptions(): array
    {
        return [
            TransformException::class => [ new TransformException('Error message!') ],
            MethodCallNotFound::class => [ new MethodCallNotFound('Error message!') ],
            CouldNotResolveNode::class => [ new CouldNotResolveNode('Error message!') ],
        ];
    }

    /**
     * @param ObjectProphecy<GenerateMember> $generateMethod
     * @return array{LanguageServerTester,LanguageServerTesterBuilder}
     */
    private function createTester(ObjectProphecy $generateMethod): array
    {
        $builder = LanguageServerTesterBuilder::createBare()
            ->enableTextDocuments()
            ->enableCommands();
        $builder->addCommand('generate', new GenerateMemberCommand(
            $builder->clientApi(),
            $builder->workspace(),
            $generateMethod->reveal(),
            InMemoryDocumentLocator::fromTextDocuments([
                TextDocumentBuilder::create('foobar')->uri(self::EXAMPLE_URI)->build()
            ])
        ));

        $tester = $builder->build();
        $tester->textDocument()->open(self::EXAMPLE_URI, self::EXAMPLE_SOURCE);

        return [$tester, $builder];
    }
}
