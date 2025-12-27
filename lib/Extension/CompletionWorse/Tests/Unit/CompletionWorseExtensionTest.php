<?php

namespace Phpactor\Extension\CompletionWorse\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\Completion\Core\Completor;
use Phpactor\Completion\Core\DocumentPrioritizer\DocumentPrioritizer;
use Phpactor\Completion\Core\TypedCompletorRegistry;
use Phpactor\Container\Container;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\ClassToFile\ClassToFileExtension;
use Phpactor\Extension\CompletionWorse\CompletionWorseExtension;
use Phpactor\Extension\Completion\CompletionExtension;
use Phpactor\Extension\ComposerAutoloader\ComposerAutoloaderExtension;
use Phpactor\Extension\ObjectRenderer\ObjectRendererExtension;
use Phpactor\Extension\Php\PhpExtension;
use Phpactor\Extension\ReferenceFinder\ReferenceFinderExtension;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;
use RuntimeException;

class CompletionWorseExtensionTest extends TestCase
{
    public function testBuild(): void
    {
        $container = $this->buildContainer();

        $completor = $container
            ->expect(CompletionExtension::SERVICE_REGISTRY, TypedCompletorRegistry::class)
            ->completorForType('php');
        assert($completor instanceof Completor);

        $completor->complete(
            TextDocumentBuilder::create('<?php array')->build(),
            ByteOffset::fromInt(8)
        );
    }

    public function testDisableCompletors(): void
    {
        $container = $this->buildContainer([
            'completion_worse.completor.worse_parameter.enabled' => false,
        ]);
        $completors = $container->get('completion_worse.completor_map');

        self::assertFalse(in_array('completion_worse.completor.constructor', $completors), 'Completor disabled');
    }

    public function testExceptionWhenSelectingUnknownSearchPriotityStrategy(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown search priority strategy "asd"');
        $container = $this->buildContainer([
            CompletionWorseExtension::PARAM_NAME_COMPLETION_PRIORITY => 'asd',
        ]);
        $container->get(DocumentPrioritizer::class);
    }

    /**
     * @param array<string,mixed> $config
     */
    private function buildContainer(array $config = []): Container
    {
        return PhpactorContainer::fromExtensions(
            [
            CompletionExtension::class,
            FilePathResolverExtension::class,
            ClassToFileExtension::class,
            ComposerAutoloaderExtension::class,
            LoggingExtension::class,
            WorseReflectionExtension::class,
            CompletionWorseExtension::class,
            SourceCodeFilesystemExtension::class,
            ReferenceFinderExtension::class,
            ObjectRendererExtension::class,
            PhpExtension::class,
        ],
            array_merge([
                FilePathResolverExtension::PARAM_APPLICATION_ROOT => __DIR__,
                ObjectRendererExtension::PARAM_TEMPLATE_PATHS => [],
            ], $config)
        );
    }
}
