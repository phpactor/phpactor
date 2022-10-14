<?php

namespace Phpactor\Extension\Symfony\Tests\Integration\Completor;

use Closure;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\Completion\Core\Completor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\Behat\BehatExtension;
use Phpactor\Extension\ClassToFile\ClassToFileExtension;
use Phpactor\Extension\Completion\CompletionExtension;
use Phpactor\Extension\ComposerAutoloader\ComposerAutoloaderExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;

class SymfonyContainerCompletorTest extends TestCase
{
    use ArraySubsetAsserts;

    /**
     * @dataProvider provideComplete
     */
    public function testComplete(string $source, Closure $assertion): void
    {
        [$source, $start] = ExtractOffset::fromSource($source);
        $suggestions = iterator_to_array($this->completor()->complete(
            TextDocumentBuilder::create($source)->language('php')->build(),
            ByteOffset::fromInt((int)$start)
        ));

    }

    /**
     * @return Generator<string,array{string,Closure(Suggestion[]):void}>
     */
    public function provideComplete(): Generator
    {
        yield 'all' => [
            <<<'EOT'
            <?php

            use Symfony\Component\DependencyInjection\Container;
            $container = new Container();
            $foobar = $container->get('foobar');
            EOT
            ,
            <<<'EOT'
            <?xml version="1.0" encoding="utf-8"?>
            <container xmlns="http://symfony.com/schema/dic/services" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">
              <services>
                <service id="service_container" class="Symfony\Component\DependencyInjection\ContainerInterface" public="true" synthetic="true"/>
              </services>
            </container>
            EOT
            ,
            /** @param Suggestion[] $suggestions */function (array $suggestions): void
            {
                \PHPStan\dumpType($suggestions);
            }
        ];
    }

    private function completor(): Completor
    {
        $container = PhpactorContainer::fromExtensions([
            WorseReflectionExtension::class,
            FilePathResolverExtension::class,
            CompletionExtension::class,
            BehatExtension::class,
            ClassToFileExtension::class,
            ComposerAutoloaderExtension::class,
            LoggingExtension::class,
        ], [
            FilePathResolverExtension::PARAM_APPLICATION_ROOT => __DIR__ . '/../../../../../..',
            BehatExtension::PARAM_CONFIG_PATH => __DIR__ .'/behat.yml',
            BehatExtension::PARAM_ENABLED => true,
        ]);


        return $container->get(CompletionExtension::SERVICE_REGISTRY)->completorForType('cucumber');
    }
}
