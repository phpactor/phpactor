<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Tests\Unit\CodeAction;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
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

class CreateClassProviderTest extends IntegrationTestCase
{
    #[DataProvider('provideClassCreateProvider')]
    #[Group('flakey')]
    public function testClassCreateProvider(string $manifest, int $expectedCount, int $expectedDiagnosticCount, bool $imprtGlobals = false): void
    {
        $this->workspace()->reset();
        $this->workspace()->loadManifest($manifest);

        $tester = $this->container([])->get(LanguageServerBuilder::class)->tester(
            ProtocolFactory::initializeParams($this->workspace()->path())
        );
        $tester->initialize();
        assert($tester instanceof LanguageServerTester);

        $subject = $this->workspace()->getContents('subject.php');
        [ $source, $offset ] = ExtractOffset::fromSource($subject);

        $tester->textDocument()->open('file:///foobar', $source);

        $result = $tester->requestAndWait(CodeActionRequest::METHOD, new CodeActionParams(
            ProtocolFactory::textDocumentIdentifier('file:///foobar'),
            new Range(
                ProtocolFactory::position(0, 0),
                PositionConverter::intByteOffsetToPosition((int)$offset, $source)
            ),
            new CodeActionContext([])
        ));

        $tester->assertSuccess($result);

        $tester->textDocument()->save('file:///foobar');

        $result = $tester->requestAndWait(CodeActionRequest::METHOD, new CodeActionParams(
            ProtocolFactory::textDocumentIdentifier('file:///foobar'),
            new Range(
                ProtocolFactory::position(0, 0),
                PositionConverter::intByteOffsetToPosition((int)$offset, $source)
            ),
            new CodeActionContext([])
        ));

        $tester->assertSuccess($result);

        self::assertCount($expectedCount, $result->result, 'Number of code actions');

        $diagnostics = $tester->transmitter()->filterByMethod('textDocument/publishDiagnostics')->shiftNotification();
        self::assertNotNull($diagnostics);
        $diagnostics = $diagnostics->params['diagnostics'];
        self::assertEquals($expectedDiagnosticCount, count($diagnostics), 'Number of diagnostics');
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideClassCreateProvider(): Generator
    {
        yield 'empty file' => [
            <<<'EOT'
                // File: subject.php

                EOT
        , 4, 0
        ];

        yield 'non empty file' => [
            <<<'EOT'
                // File: subject.php
                <?php

                EOT
        , 0, 0
        ];
    }
}
