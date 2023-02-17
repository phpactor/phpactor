<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Tests\Unit\LspCommand;

use Phpactor\CodeTransform\Domain\Refactor\GenerateDecorator;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\GenerateDecoratorCommand;
use Phpactor\LanguageServerProtocol\ApplyWorkspaceEditResult;
use Phpactor\LanguageServer\LanguageServerTesterBuilder;
use Phpactor\LanguageServer\Test\LanguageServerTester;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\LanguageServerBridge\Converter\TextEditConverter;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class GenerateDecoratorCommandTest extends TestCase
{
    use ProphecyTrait;
    const EXAMPLE_SOURCE = '<?php interface SomeInterface{} class ClassesAreCool implements SomeInterface {}';
    const EXAMPLE_URI = 'file:///file.php';
    const EXAMPLE_OFFSET = 48;

    public function testSuccessfulCall(): void
    {
        $textEdits = new TextEdits(TextEdit::create(self::EXAMPLE_OFFSET, 1, 'test'));

        $generateAccessors = $this->prophesize(GenerateDecorator::class);
        $generateAccessors->getTextEdits(Argument::type(SourceCode::class), 'SomeInterface')
            ->shouldBeCalled()
            ->willReturn($textEdits);

        [$tester, $builder] = $this->createTester($generateAccessors);
        $tester->workspace()->executeCommand('generate_decorator', [
            self::EXAMPLE_URI,
            'SomeInterface'
        ]);
        $builder->responseWatcher()->resolveLastResponse(new ApplyWorkspaceEditResult(true));

        $applyEdit = $builder->transmitter()
                             ->filterByMethod('workspace/applyEdit')
                             ->shiftRequest();

        self::assertNotNull($applyEdit);
        self::assertEquals([
            'edit' => new WorkspaceEdit([
                self::EXAMPLE_URI => TextEditConverter::toLspTextEdits(
                    $textEdits,
                    self::EXAMPLE_SOURCE
                )
            ]),
            'label' => 'Generate decoration'
        ], $applyEdit->params);
    }

    /**
     * @return array{LanguageServerTester,LanguageServerTesterBuilder}
     */
    private function createTester(ObjectProphecy $generateAccessors): array
    {
        $builder = LanguageServerTesterBuilder::createBare()
            ->enableTextDocuments()
            ->enableCommands();
        $builder->addCommand('generate_decorator', new GenerateDecoratorCommand(
            $builder->clientApi(),
            $builder->workspace(),
            $generateAccessors->reveal(),
        ));

        $tester = $builder->build();
        $tester->textDocument()->open(self::EXAMPLE_URI, self::EXAMPLE_SOURCE);

        return [$tester, $builder];
    }
}
