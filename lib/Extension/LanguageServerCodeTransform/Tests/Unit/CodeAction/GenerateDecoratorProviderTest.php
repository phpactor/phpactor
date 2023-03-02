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
     * @dataProvider provideGenerateDecoratorProvider
     * @group flakey
     */
    public function testGenerateDecoratorProvider(string $source, int $expectedCount): void
    {
        $this->workspace()->reset();
        [$source, $offset] = ExtractOffset::fromSource($source);

        $tester = $this->container([])->get(LanguageServerBuilder::class)->tester(
            ProtocolFactory::initializeParams($this->workspace()->path())
        );
        $tester->initialize();
        $tester->textDocument()->open('file:///foobar', $source);

        $result = $tester->requestAndWait(CodeActionRequest::METHOD, new CodeActionParams(
            ProtocolFactory::textDocumentIdentifier('file:///foobar'),
            new Range(
                PositionConverter::intByteOffsetToPosition((int)$offset, $source),
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
    public function provideGenerateDecoratorProvider(): Generator
    {
        yield 'class with no interfaces' => [
            <<<'EOT'
                <?php
                class Foo<>baz {}

                EOT
        , 0
        ];

        yield 'class with one interface' => [
            <<<'EOT'
                <?php
                interface SomeInterface {
                    public function foo(): void;
                }

                class FooBar implements So<>meInterface {}

                EOT
        , 1
        ];

        yield 'interface provides no actions' => [
            <<<'EOT'
                <?php
                interface S<>omeInterface {public function foo(): void {}}

                EOT
        , 0
        ];

        yield 'class with multiple interfaces' => [
            <<<'EOT'
                <?php
                interface SomeInterface {public function foo(): void {}}
                interface OtherInterface {public function foo(): void {}}
                class FooBar implements SomeInterface, Ot<>herInterface {}

                EOT
        , 1
        ];
    }
}
