<?php

namespace Phpactor\Extension\WorseReflection;

use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Bridge\PsrLog\PsrLogger;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StubSourceLocator;
use Phpactor\WorseReflection\Bridge\Phpactor\ClassToFileSourceLocator;
use Phpactor\WorseReflection\ReflectorBuilder;
use Phpactor\Extension\Extension;
use Phpactor\Extension\Schema;
use Phpactor\Extension\ContainerBuilder;
use Phpactor\Extension\Container;

class WorseReflectionExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function configure(Schema $schema)
    {
        $schema->setDefaults([
            'reflection.stub_directory' => __DIR__ . '/../../../vendor/jetbrains/phpstorm-stubs',
        ]);
    }

    public function load(ContainerBuilder $container)
    {
        $container->register('reflection.reflector', function (Container $container) {
            $builder = ReflectorBuilder::create()
                ->enableCache()
                ->enableContextualSourceLocation();

            foreach (array_keys($container->getServiceIdsForTag('reflection.source_locator')) as $locatorId) {
                $builder->addLocator($container->get($locatorId));
            }

            $builder->withLogger(new PsrLogger($container->get('monolog.logger')));

            return $builder->build();
        });

        $container->register('reflection.locator.stub', function (Container $container) {
            return new StubSourceLocator(
                ReflectorBuilder::create()->build(),
                $container->getParameter('reflection.stub_directory'),
                $container->getParameter('cache_dir')
            );
        }, [ 'reflection.source_locator' => []]);

        $container->register('reflection.locator.worse', function (Container $container) {
            return new ClassToFileSourceLocator($container->get('class_to_file.class_to_file'));
        }, [ 'reflection.source_locator' => []]);
    }
}
