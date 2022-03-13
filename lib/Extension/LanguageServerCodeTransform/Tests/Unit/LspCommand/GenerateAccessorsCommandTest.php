<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Tests\Unit\LspCommand;

use Phpactor\CodeTransform\Domain\Refactor\GenerateAccessor;
use Phpactor\LanguageServerProtocol\ApplyWorkspaceEditResponse;
use Phpactor\LanguageServer\LanguageServerTesterBuilder;
use Phpactor\LanguageServer\Test\LanguageServerTester;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\TextDocument\TextDocumentLocator\InMemoryDocumentLocator;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\LanguageServerBridge\Converter\TextEditConverter;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\GenerateAccessorsCommand;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class GenerateAccessorsCommandTest extends TestCase
{
    use ProphecyTrait;
    const EXAMPLE_SOURCE = '<?php ';
    const EXAMPLE_URI = 'file:///file.php';
    const EXAMPLE_OFFSET = 5;

    public function testSuccessfulCall(): void
    {
        $textEdits = new TextEdits(TextEdit::create(self::EXAMPLE_OFFSET, 1, 'test'));

        $generateAccessors = $this->prophesize(GenerateAccessor::class);
        $generateAccessors->generate(
            Argument::type(SourceCode::class),
            [
                'foo',
            ],
            self::EXAMPLE_OFFSET
        )
            ->shouldBeCalled()
            ->willReturn($textEdits);

        [$tester, $builder] = $this->createTester($generateAccessors);
        $tester->workspace()->executeCommand('generate', [
            self::EXAMPLE_URI,
            self::EXAMPLE_OFFSET,
            [
                'foo',
            ],
        ]);
        $builder->responseWatcher()->resolveLastResponse(new ApplyWorkspaceEditResponse(true));

        $applyEdit = $builder->transmitter()->filterByMethod('workspace/applyEdit')->shiftRequest();

        self::assertNotNull($applyEdit);
        self::assertEquals([
            'edit' => new WorkspaceEdit([
                self::EXAMPLE_URI => TextEditConverter::toLspTextEdits(
                    $textEdits,
                    self::EXAMPLE_SOURCE
                )
            ]),
            'label' => 'Generate accessors'
        ], $applyEdit->params);
    }

    /**
     * @return {LanguageServerTester,LanguageServerTesterBuilder]
     */
    private function createTester(ObjectProphecy $generateAccessors): array
    {
        $builder = LanguageServerTesterBuilder::createBare()
            ->enableTextDocuments()
            ->enableCommands();
        $builder->addCommand('generate', new GenerateAccessorsCommand(
            $builder->clientApi(),
            $builder->workspace(),
            $generateAccessors->reveal(),
            InMemoryDocumentLocator::fromTextDocuments([
                TextDocumentBuilder::create('foobar')->uri(self::EXAMPLE_URI)->build()
            ])
        ));
        
        $tester = $builder->build();
        $tester->textDocument()->open(self::EXAMPLE_URI, self::EXAMPLE_SOURCE);

        return [$tester, $builder];
    }
}
