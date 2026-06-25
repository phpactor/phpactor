<?php

namespace Phpactor\Extension\LanguageServerMago\Tests;

use Phpactor\Container\Container;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Extension\LanguageServerMago\LanguageServerMagoExtension;
use Phpactor\Extension\LanguageServerMago\Provider\MagoDiagnosticProvider;
use Phpactor\Extension\Logger\LoggingExtension;
use PHPUnit\Framework\TestCase;

class LanguageServerMagoExtensionTest extends TestCase
{
    public function testRegistersBothDiagnosticProviders(): void
    {
        $container = $this->getContainer();

        $names = [];
        foreach (array_keys($container->getServiceIdsForTag(LanguageServerExtension::TAG_DIAGNOSTICS_PROVIDER)) as $id) {
            $provider = $container->get($id);
            self::assertInstanceOf(MagoDiagnosticProvider::class, $provider);
            $names[] = $provider->name();
        }

        self::assertContains('mago', $names);
        self::assertContains('mago-lint', $names);
    }

    /**
     * @param array<string, mixed> $params
     */
    private function getContainer(array $params = []): Container
    {
        return PhpactorContainer::fromExtensions(
            [
                FilePathResolverExtension::class,
                LoggingExtension::class,
                LanguageServerMagoExtension::class,
            ],
            $params,
        );
    }
}
