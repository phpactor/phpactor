<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Tests\Unit\CodeAction;

use Generator;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\Extension\LanguageServerCodeTransform\Tests\IntegrationTestCase;
use Phpactor\LanguageServerProtocol\CodeActionContext;
use Phpactor\LanguageServerProtocol\CodeActionParams;
use Phpactor\LanguageServerProtocol\CodeActionRequest;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServer\LanguageServerBuilder;
use Phpactor\LanguageServer\Test\LanguageServerTester;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\TestUtils\ExtractOffset;

class GenerateDecoratorProviderTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideClassCreateProvider
     * @group flakey
     */
    public function testClassCreateProvider(string $manifest, int $expectedCount): void
    {
        $this->workspace()->reset();
        $this->workspace()->loadManifest($manifest);

        $tester = $this->container([])->get(LanguageServerBuilder::class)->tester(
            ProtocolFactory::initializeParams($this->workspace()->path())
        );
        $tester->initialize();
        assert($tester instanceof LanguageServerTester);

        $FooBar = $this->workspace()->getContents('FooBar.php');
        [ $source, $offset ] = ExtractOffset::fromSource($FooBar);

        $tester->textDocument()->open('file:///foobar', $source);

        $result = $tester->requestAndWait(CodeActionRequest::METHOD, new CodeActionParams(
            ProtocolFactory::textDocumentIdentifier('file:///foobar'),
            new Range(
                ProtocolFactory::position(0, 8),
                PositionConverter::intByteOffsetToPosition((int)$offset, $source)
            ),
            new CodeActionContext([])
        ));

        $tester->assertSuccess($result);

        $tester->textDocument()->save('file:///foobar', $source);

        $result = $tester->requestAndWait(CodeActionRequest::METHOD, new CodeActionParams(
            ProtocolFactory::textDocumentIdentifier('file:///foobar'),
            new Range(
                ProtocolFactory::position(0, 8),
                PositionConverter::intByteOffsetToPosition((int)$offset, $source)
            ),
            new CodeActionContext([])
        ));

        $tester->assertSuccess($result);

        self::assertCount($expectedCount, $result->result, 'Number of code actions');
    }

    /**
     * @return Generator<mixed>
     */
    public function provideClassCreateProvider(): Generator
    {
        yield 'class with no interfaces' => [
            <<<'EOT'
                // File: FooBar.php
                <?php
                class FooBar {}

                EOT
        , 0
        ];

        yield 'class with one interface' => [
            <<<'EOT'
                // File: FooBar.php
                <?php
                class FooBar implements SomeInterface {}

                EOT
        , 1
        ];

        yield 'class with multiple interfaces' => [
            <<<'EOT'
                // File: FooBar.php
                <?php
                class FooBar implements SomeInterface, OtherInterface {}

                EOT
        , 2
        ];
    }
}
