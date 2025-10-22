<?php

namespace Phpactor\Extension\LanguageServerReferenceFinder\Tests\Unit;

use Phpactor\Extension\LanguageServerReferenceFinder\Tests\Extension\TestIndexerExtension;
use Phpactor\LanguageServerProtocol\ReferenceContext;
use Phpactor\LanguageServerProtocol\TextDocumentIdentifier;
use PHPUnit\Framework\TestCase;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\LanguageServerBridge\LanguageServerBridgeExtension;
use Phpactor\Extension\LanguageServerReferenceFinder\LanguageServerReferenceFinderExtension;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\ReferenceFinder\ReferenceFinderExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\LanguageServer\LanguageServerBuilder;
use Phpactor\LanguageServer\Test\LanguageServerTester;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\TestUtils\Workspace;

class LanguageServerReferenceFinderExtensionTest extends TestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    public function testDefinition(): void
    {
        $tester = $this->createTester();
        $tester->textDocument()->open(__FILE__, (string)file_get_contents(__FILE__));

        $response = $tester->requestAndWait('textDocument/definition', [
            'textDocument' => new TextDocumentIdentifier(__FILE__),
            'position' => [],
        ]);
        $this->assertNull($response->result, 'Definition was not found');
    }

    public function testTypeDefinition(): void
    {
        $tester = $this->createTester();
        $tester->textDocument()->open(__FILE__, (String)file_get_contents(__FILE__));

        $response = $tester->requestAndWait('textDocument/typeDefinition', [
            'textDocument' => new TextDocumentIdentifier(__FILE__),
            'position' => [
            ],
        ]);
        $this->assertNull($response->result, 'Type was not found');
    }

    public function testReferenceFinder(): void
    {
        $tester = $this->createTester();
        $tester->textDocument()->open(__FILE__, (string)file_get_contents(__FILE__));

        $response = $tester->requestAndWait('textDocument/references', [
            'textDocument' => new TextDocumentIdentifier(__FILE__),
            'position' => [
                'line' => 0,
                'character' => 0,
            ],
            'context' => new ReferenceContext(false),
        ]);
        $tester->assertSuccess($response);
        $this->assertIsArray($response->result, 'Returned empty references');
    }

    protected function workspace(): Workspace
    {
        return Workspace::create(__DIR__ . '/../Workspace');
    }

    private function createTester(): LanguageServerTester
    {
        $container = PhpactorContainer::fromExtensions([
            LoggingExtension::class,
            LanguageServerExtension::class,
            LanguageServerReferenceFinderExtension::class,
            ReferenceFinderExtension::class,
            FilePathResolverExtension::class,
            LanguageServerBridgeExtension::class,
            TestIndexerExtension::class,
        ], [
            LanguageServerExtension::PARAM_ENABLE_TRUST_CHECK => false,
        ]);

        $builder = $container->get(LanguageServerBuilder::class);
        $this->assertInstanceOf(LanguageServerBuilder::class, $builder);

        return $builder->tester(ProtocolFactory::initializeParams(__DIR__));
    }
}
