<?php

namespace Phpactor\Container;

use PhpBench\DependencyInjection\Container;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\SourceCodeLocator\ChainSourceLocator;
use Phpactor\WorseReflection\Logger\PsrLogger;
use Phpactor\WorseReflection\SourceCodeLocator\StubSourceLocator;
use Phpactor\WorseReflection\SourceCodeLocator\StringSourceLocator;
use Phpactor\WorseReflection\SourceCode;
use Phpactor\TypeInference\Adapter\WorseReflection\WorseSourceCodeLocator;
use PhpBench\DependencyInjection\ExtensionInterface;

class WorseReflectionExtension implements ExtensionInterface
{
    public function getDefaultConfig()
    {
        return [
            'reflection.stub_directory' => __DIR__ . '/../../vendor/jetbrains/phpstorm-stubs',
        ];
    }

    public function load(Container $container)
    {
        $container->register('reflection.reflector', function (Container $container) {
            $locators = [];

            foreach (array_keys($container->getServiceIdsForTag('reflection.source_locator')) as $locatorId) {
                $locators[] = $container->get($locatorId);
            }
            return Reflector::create(
                new ChainSourceLocator($locators),
                new PsrLogger($container->get('monolog.logger'))
            );
        });

        $container->register('reflection.locator.stub', function (Container $container) {
            return new StubSourceLocator(
                // TODO: we do not need the location facility of the reflector in this case
                //       need to separate responsiblities
                Reflector::create(new StringSourceLocator(SourceCode::fromString(''))),
                $container->getParameter('reflection.stub_directory'),
                $container->getParameter('cache_dir')
            );
        }, [ 'reflection.source_locator' => []]);

        $container->register('reflection.locator.worse', function (Container $container) {
            return new WorseSourceCodeLocator(
                $container->get('type_inference.source_code_loader')
            );
        }, [ 'reflection.source_locator' => []]);
    }
}
