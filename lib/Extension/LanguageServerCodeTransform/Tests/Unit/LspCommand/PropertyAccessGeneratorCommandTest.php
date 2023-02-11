<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Tests\Unit\LspCommand;

use Phpactor\CodeTransform\Domain\Refactor\PropertyAccessGenerator;
use Phpactor\LanguageServerProtocol\ApplyWorkspaceEditResult;
use Phpactor\LanguageServer\LanguageServerTesterBuilder;
use Phpactor\LanguageServer\Test\LanguageServerTester;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\LanguageServerBridge\Converter\TextEditConverter;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\PropertyAccessGeneratorCommand;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class PropertyAccessGeneratorCommandTest extends TestCase
{
    use ProphecyTrait;
    const EXAMPLE_SOURCE = '<?php ';
    const EXAMPLE_URI = 'file:///file.php';
    const EXAMPLE_OFFSET = 5;

    public function testSuccessfulCall(): void
    {
        $textEdits = new TextEdits(TextEdit::create(self::EXAMPLE_OFFSET, 1, 'test'));

        $generateAccessors = $this->prophesize(PropertyAccessGenerator::class);
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
            'label' => 'Generate accessors'
        ], $applyEdit->params);
    }

    /**
     * @param ObjectProphecy<PropertyAccessGenerator> $generateAccessors
     * @return array{LanguageServerTester,LanguageServerTesterBuilder}
     */
    private function createTester(ObjectProphecy $generateAccessors): array
    {
        $builder = LanguageServerTesterBuilder::createBare()
            ->enableTextDocuments()
            ->enableCommands();
        $builder->addCommand('generate', new PropertyAccessGeneratorCommand(
            $builder->clientApi(),
            $builder->workspace(),
            $generateAccessors->reveal(),
            'Generate accessors'
        ));

        $tester = $builder->build();
        $tester->textDocument()->open(self::EXAMPLE_URI, self::EXAMPLE_SOURCE);

        return [$tester, $builder];
    }
}
