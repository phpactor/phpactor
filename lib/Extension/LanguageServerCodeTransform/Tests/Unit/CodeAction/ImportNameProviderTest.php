<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Tests\Unit\CodeAction;

use Generator;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\Extension\LanguageServerCodeTransform\LanguageServerCodeTransformExtension;
use Phpactor\Extension\LanguageServerCodeTransform\Tests\IntegrationTestCase;
use Phpactor\LanguageServerProtocol\CodeAction;
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
    public function testImportProvider(string $manifest, int $expectedCount, int $expectedDiagnosticCount, bool $imprtGlobals = false): void
    {
        $this->workspace()->reset();
        $this->workspace()->loadManifest($manifest);

        $tester = $this->container([
            LanguageServerCodeTransformExtension::PARAM_IMPORT_GLOBALS => $imprtGlobals,
            LanguageServerCodeTransformExtension::PARAM_REPORT_NON_EXISTING_NAMES => true
        ])->get(LanguageServerBuilder::class)->tester(
            ProtocolFactory::initializeParams($this->workspace()->path())
        );
        $tester->initialize();
        assert($tester instanceof LanguageServerTester);

        $subject = $this->workspace()->getContents('subject.php');
        [ $source, $offset ] = ExtractOffset::fromSource($subject);

        $tester->textDocument()->open('file:///foobar', $source);

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

        $tester->assertSuccess($result);

        self::assertCount($expectedCount, array_filter($result->result, function (CodeAction $action) {
            return $action->kind == 'quickfix.import_class';
        }), 'Number of code actions');
        $tester->textDocument()->save('file:///foobar');

        $diagnostics = $tester->transmitter()->filterByMethod('textDocument/publishDiagnostics')->shiftNotification();
        self::assertNotNull($diagnostics);
        $diagnostics = $diagnostics->params['diagnostics'];
        self::assertEquals($expectedDiagnosticCount, count($diagnostics), 'Number of diagnostics');
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
            , 0, 1
        ];

        yield 'code actions + diagnostic for namespaced non-existant class' => [
            <<<'EOT'
                // File: subject.php
                <?php namespace Bar; new MissingNameFoo();'
                EOT
            , 0, 1
        ];

        yield 'code action and diagnostic for missing global class name with import globals' => [
            <<<'EOT'
                // File: subject.php
                <?php namespace Foobar; function foobar(): Generator { yield 12; }'
                // File: Generator.php
                <?php class Generator {}
                EOT
            , 1, 1, true
        ];

        yield 'code action and diagnostic for missing global class name without import globals' => [
            <<<'EOT'
                // File: subject.php
                <?php namespace Foobar; function foobar(): Generator { yield 12; }'
                // File: Generator.php
                <?php class Generator {}
                EOT
            , 1, 1, false
        ];

        yield 'no code action or diagnostics for missing global function name' => [
            <<<'EOT'
                // File: subject.php
                <?php namespace Foobar; sprintf('foo %s', 'bar')
                EOT
            , 0, 0
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
            , 0, 0
        ];

        yield 'built in global funtion' => [
            <<<'EOT'
                // File: subject.php
                <?php

                namespace Phpactor\Extension;

                $bar = [];
                explode(array_keys($bar));
                EOT
            , 0, 0
        ];

        yield 'built in global funtion with import globals' => [
            <<<'EOT'
                // File: functions.php
                // File: subject.php
                <?php

                namespace Phpactor\Extension;

                $bar = [];
                explode(array_keys($bar));
                EOT
            , 2, 2, true
        ];

        yield 'constant' => [
            <<<'EOT'
                // File: subject.php
                <?php

                namespace Phpactor\Extension;

                if (INF) {
                }
                EOT
            , 0, 0, true
        ];
    }
}
