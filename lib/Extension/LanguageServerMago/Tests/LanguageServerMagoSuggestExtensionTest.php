<?php

namespace Phpactor\Extension\LanguageServerMago\Tests;

use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\Configuration\ConfigurationExtension;
use Phpactor\Extension\LanguageServerMago\LanguageServerMagoSuggestExtension;
use PHPUnit\Framework\TestCase;

class LanguageServerMagoSuggestExtensionTest extends TestCase
{
    public function testRegistersSuggestor(): void
    {
        $container = PhpactorContainer::fromExtensions([LanguageServerMagoSuggestExtension::class]);

        self::assertArrayHasKey(
            'language_server_mago.suggest',
            $container->getServiceIdsForTag(ConfigurationExtension::TAG_SUGGESTOR),
        );
    }
}
