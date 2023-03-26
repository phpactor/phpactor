<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Tests\Unit\LspCommand;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeTransform\Domain\Diagnostics;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Domain\Transformer;
use Phpactor\CodeTransform\Domain\Transformers;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\TransformCommand;
use Phpactor\LanguageServerProtocol\ApplyWorkspaceEditResult;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\LanguageServerTesterBuilder;
use Phpactor\TextDocument\TextEdits;
use function Amp\Promise\wait;

class TransformCommandTest extends TestCase
{
    const EXAMPLE_TRANSFORM_NAME = 'test_transform';

    public function testAppliesTransform(): void
    {
        $testTransformer = new TestTransformer();
        $transformers = new Transformers([
            self::EXAMPLE_TRANSFORM_NAME => $testTransformer
        ]);
        $tester = LanguageServerTesterBuilder::create();
        $tester->addCommand('transform', new TransformCommand(
            $tester->clientApi(),
            $tester->workspace(),
            $transformers
        ));
        $watcher = $tester->responseWatcher();
        $tester = $tester->build();
        $tester->textDocument()->open('file:///foobar', 'foobar');
        $promise = $tester->workspace()->executeCommand('transform', [
            'file:///foobar',
            self::EXAMPLE_TRANSFORM_NAME
        ]);
        $watcher->resolveLastResponse(new ApplyWorkspaceEditResult(true));
        $response = wait($promise);
        self::assertInstanceOf(ResponseMessage::class, $response);
        self::assertInstanceOf(ApplyWorkspaceEditResult::class, $response->result);

        self::assertNotNull($testTransformer->code);
        self::assertEquals('/foobar', $testTransformer->code->uri()->path());
    }
}

class TestTransformer implements Transformer
{
    public SourceCode $code;

    public function transform(SourceCode $code): TextEdits
    {
        $this->code = $code;
        return TextEdits::none();
    }


    public function diagnostics(SourceCode $code): Diagnostics
    {
        return Diagnostics::none();
    }
}
