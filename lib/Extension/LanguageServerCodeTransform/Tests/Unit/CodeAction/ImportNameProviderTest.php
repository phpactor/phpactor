<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Tests\Unit\CodeAction;

use Closure;
use Generator;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\Extension\LanguageServerCodeTransform\LanguageServerCodeTransformExtension;
use Phpactor\Extension\LanguageServerCodeTransform\Tests\IntegrationTestCase;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\LanguageServerProtocol\CodeActionContext;
use Phpactor\LanguageServerProtocol\CodeActionParams;
use Phpactor\LanguageServerProtocol\CodeActionRequest;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServer\LanguageServerBuilder;
use Phpactor\LanguageServer\Test\LanguageServerTester;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\TestUtils\ExtractOffset;
use function Amp\Promise\wait;
use function Amp\delay;

class ImportNameProviderTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideImportProvider
     * @group flakey
     */
    public function testImportProvider(string $manifest, Closure $assertion, bool $imprtGlobals = false): void
    {
        $this->workspace()->reset();
        $this->workspace()->loadManifest($manifest);

        $tester = $this->container([
            WorseReflectionExtension::PARAM_IMPORT_GLOBALS => $imprtGlobals,
            LanguageServerCodeTransformExtension::PARAM_REPORT_NON_EXISTING_NAMES => true
        ])->get(LanguageServerBuilder::class)->tester(
            ProtocolFactory::initializeParams($this->workspace()->path())
        );

        assert($tester instanceof LanguageServerTester);
        $subject = $this->workspace()->getContents('subject.php');
        [ $source, $offset ] = ExtractOffset::fromSource($subject);

        $tester->textDocument()->open('file:///foobar', $source);
        $tester->initialize();

        // give the indexer a chance to index
        wait(delay(10));

        $result = $tester->requestAndWait(CodeActionRequest::METHOD, new CodeActionParams(
            ProtocolFactory::textDocumentIdentifier('file:///foobar'),
            new Range(
                ProtocolFactory::position(0, 0),
                PositionConverter::intByteOffsetToPosition((int)$offset, $source)
            ),
            new CodeActionContext([])
        ));
        self::assertNotNull($result);
        $tester->assertSuccess($result);


        $transmitter = $tester->transmitter()->filterByMethod('textDocument/publishDiagnostics');
        $diagnostics = $transmitter->shiftNotification();
        $diagnostics = $diagnostics->params['diagnostics'] ?? [];
        $assertion($result->result, $diagnostics);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideImportProvider(): Generator
    {
        // this test is very flakey
        //yield 'code action + diagnostic for non-imported name' => [
        //    <<<'EOT'
        //        // File: subject.php
        //        <?php new MissingName();'
        //        // File: Foobar/MissingName.php
        //        <?php namespace Foobar; class MissingName {}
        //        EOT
        //, 1, 1
        //];

        yield 'code actions + diagnostic for non-existant class' => [
            <<<'EOT'
                // File: subject.php
                <?php new MissingNameFoo();'
                EOT
            , function (array $codeActions, array $diagnostics): void {
                self::assertCount(0, $codeActions);
                self::assertCount(1, $diagnostics);
            }

        ];

        yield 'code actions + diagnostic for namespaced non-existant class' => [
            <<<'EOT'
                // File: subject.php
                <?php namespace Bar; new MissingNameFoo();'
                EOT
            , function (array $codeActions, array $diagnostics): void {
                self::assertCount(0, $codeActions);
                self::assertCount(1, $diagnostics);
            }
        ];

        yield 'code action and diagnostic for missing global class name' => [
            <<<'EOT'
                // File: subject.php
                <?php namespace Foobar; function foobar(): Generator { yield 12; }'
                // File: Generator.php
                <?php class Generator {}
                EOT
            , function (array $codeActions, array $diagnostics): void {
                self::assertCount(1, $codeActions);
                self::assertCount(1, $diagnostics);
            }, false
        ];

        yield 'no code action or diagnostics for missing global function name' => [
            <<<'EOT'
                // File: subject.php
                <?php namespace Foobar;
                sprintf('foo %s', 'bar');
                EOT
            , function (array $codeActions, array $diagnostics): void {
                self::assertCount(0, $codeActions);
                self::assertCount(0, $diagnostics);
            }, false
        ];

        yield 'no diagnostics for class declared in same namespace' => [
            <<<'EOT'
                // File: subject.php
                <?php

                namespace Phpactor\Extension;

                class Test
                {
                    public function testBar(): Bar
                    {
                        new Bar();
                    }
                }

                class Bar
                {
                }
                EOT
            , function (array $codeActions, array $diagnostics): void {
                self::assertCount(0, $codeActions);
                self::assertCount(0, $diagnostics);
            }
        ];

        yield 'built in global funtion' => [
            <<<'EOT'
                // File: subject.php
                <?php

                namespace Phpactor\Extension;

                sprintf('foo', 'bar');
                EOT
            , function (array $codeActions, array $diagnostics): void {
                self::assertCount(0, $codeActions);
                self::assertCount(0, $diagnostics);
            }
        ];

        yield 'built in global function with import globals' => [
            <<<'EOT'
                // File: functions.php
                // File: subject.php
                <?php

                namespace Phpactor\Extension;

                $bar = [];
                explode(array_keys($bar));
                EOT
            , function (array $codeActions, array $diagnostics): void {
                self::assertCount(3, $codeActions);
                self::assertEquals('Import function "explode"', $codeActions[1]->title);
                self::assertEquals('Import function "array_keys"', $codeActions[2]->title);
                self::assertEquals('Import all unresolved names', $codeActions[0]->title);
                self::assertCount(2, $diagnostics);
            }, true
        ];

        yield 'constant' => [
            <<<'EOT'
                // File: subject.php
                <?php

                namespace Phpactor\Extension;

                if (INF) {
                }
                EOT
            , function (array $codeActions, array $diagnostics): void {
                self::assertCount(0, $codeActions);
                self::assertCount(0, $diagnostics);
            }, true
        ];
    }
}
