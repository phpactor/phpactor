<?php

namespace Phpactor\Extension\WorseReflection;

use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\ClassToFile\ClassToFileExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\WorseReflection\Bridge\Phpactor\MemberProvider\DocblockMemberProvider;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\AssignmentToMissingPropertyProvider;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\MissingDocblockReturnTypeProvider;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\MissingMethodProvider;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\MissingReturnTypeProvider;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\UnresolvableNameProvider;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\UnusedImportProvider;
use Phpactor\WorseReflection\Core\Cache;
use Phpactor\WorseReflection\Core\Cache\TtlCache;
use Phpactor\WorseReflection\Core\SourceCodeLocator\NativeReflectionFunctionSourceLocator;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StubSourceLocator;
use Phpactor\WorseReflection\Bridge\Phpactor\ClassToFileSourceLocator;
use Phpactor\WorseReflection\ReflectorBuilder;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Container;
use Phpactor\WorseReflection\Bridge\TolerantParser\Parser\CachedParser;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflector\TolerantFactory;

class WorseReflectionExtension implements Extension
{
    const SERVICE_REFLECTOR = 'worse_reflection.reflector';
    const TAG_SOURCE_LOCATOR = 'worse_reflection.source_locator';
    const TAG_FRAME_WALKER = 'worse_reflection.frame_walker';
    const TAG_MEMBER_PROVIDER = 'worse_reflection.member_provider';
    const PARAM_ENABLE_CACHE = 'worse_reflection.enable_cache';
    const PARAM_STUB_DIR = 'worse_reflection.stub_dir';
    const PARAM_STUB_CACHE_DIR = 'worse_reflection.cache_dir';
    const PARAM_CACHE_LIFETIME = 'worse_reflection.cache_lifetime';
    const PARAM_ENABLE_CONTEXT_LOCATION = 'worse_reflection.enable_context_location';
    const SERVICE_PARSER = 'worse_reflection.tolerant_parser';
    const TAG_DIAGNOSTIC_PROVIDER = 'worse_reflection.diagnostics_provider';
    const TAG_MEMBER_TYPE_RESOLVER = 'worse_reflection.member_type_resolver';
    const PARAM_IMPORT_GLOBALS = 'language_server_code_transform.import_globals';

    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_IMPORT_GLOBALS => false,
            self::PARAM_ENABLE_CACHE => true,
            self::PARAM_CACHE_LIFETIME => 1.0,
            self::PARAM_ENABLE_CONTEXT_LOCATION => true,
            self::PARAM_STUB_CACHE_DIR => '%cache%/worse-reflection',
            self::PARAM_STUB_DIR => '%application_root%/vendor/jetbrains/phpstorm-stubs',
        ]);
        $schema->setDescriptions([
            self::PARAM_ENABLE_CACHE => 'If reflection caching should be enabled',
            self::PARAM_CACHE_LIFETIME => 'If caching is enabled, limit the amount of time a cache entry can stay alive',
            self::PARAM_ENABLE_CONTEXT_LOCATION => <<<'EOT'
                If source code is passed to a ``Reflector`` then temporarily make it available as a
                source location. Note this should NOT be enabled if the source code can be
                located in another (e.g. when running a Language Server)
                EOT
        ,
            self::PARAM_STUB_DIR => 'Location of the core PHP stubs - these will be scanned and cached on the first request',
            self::PARAM_STUB_CACHE_DIR => 'Cache directory for stubs',
            self::PARAM_IMPORT_GLOBALS => 'Show hints for non-imported global classes and functions',
        ]);
    }

    public function load(ContainerBuilder $container): void
    {
        $this->registerReflection($container);
        $this->registerSourceLocators($container);
        $this->registerMemberProviders($container);
        $this->registerDiagnosticProviders($container);
    }

    private function registerReflection(ContainerBuilder $container): void
    {
        $container->register(self::SERVICE_REFLECTOR, function (Container $container) {
            $builder = ReflectorBuilder::create()
                ->withSourceReflectorFactory(new TolerantFactory($container->get(self::SERVICE_PARSER)))
                ->cacheLifetime($container->getParameter(self::PARAM_CACHE_LIFETIME));

            if ($container->getParameter(self::PARAM_ENABLE_CONTEXT_LOCATION)) {
                $builder->enableContextualSourceLocation();
            }

            if ($container->getParameter(self::PARAM_ENABLE_CACHE)) {
                $builder->enableCache();
                $builder->withCache($container->get(Cache::class));
            }

            foreach ($container->getServiceIdsForTag(self::TAG_SOURCE_LOCATOR) as $serviceId => $attrs) {
                $builder->addLocator($container->get($serviceId), $attrs['priority'] ?? 0);
            }

            foreach (array_keys($container->getServiceIdsForTag(self::TAG_FRAME_WALKER)) as $serviceId) {
                $builder->addFrameWalker($container->get($serviceId));
            }

            foreach (array_keys($container->getServiceIdsForTag(self::TAG_MEMBER_TYPE_RESOLVER)) as $serviceId) {
                $memberTypeResolver = $container->get($serviceId);
                if (null === $memberTypeResolver) {
                    continue;
                }
                $builder->addMemberContextResolver($memberTypeResolver);
            }

            foreach (array_keys($container->getServiceIdsForTag(self::TAG_MEMBER_PROVIDER)) as $serviceId) {
                $memberProvider = $container->get($serviceId);
                if (null === $memberProvider) {
                    continue;
                }
                $builder->addMemberProvider($memberProvider);
            }
            foreach (array_keys($container->getServiceIdsForTag(self::TAG_DIAGNOSTIC_PROVIDER)) as $serviceId) {
                $builder->addDiagnosticProvider($container->get($serviceId));
            }

            $builder->withLogger(
                LoggingExtension::channelLogger($container, 'wr')
            );

            return $builder->build();
        });

        $container->register(self::SERVICE_PARSER, function (Container $container) {
            return new CachedParser($container->get(Cache::class));
        });

        $container->register(Cache::class, function (Container $container) {
            return new TtlCache($container->getParameter(self::PARAM_CACHE_LIFETIME));
        });
    }

    private function registerSourceLocators(ContainerBuilder $container): void
    {
        $container->register('worse_reflection.locator.stub', function (Container $container) {
            $resolver = $container->get(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER);
            return new StubSourceLocator(
                ReflectorBuilder::create()->build(),
                $resolver->resolve($container->getParameter(self::PARAM_STUB_DIR)),
                $resolver->resolve($container->getParameter(self::PARAM_STUB_CACHE_DIR))
            );
        }, [ self::TAG_SOURCE_LOCATOR => []]);

        $container->register('worse_reflection.locator.function', function (Container $container) {
            return new NativeReflectionFunctionSourceLocator();
        }, [ self::TAG_SOURCE_LOCATOR => []]);

        $container->register('worse_reflection.locator.worse', function (Container $container) {
            return new ClassToFileSourceLocator($container->get(ClassToFileExtension::SERVICE_CONVERTER));
        }, [ self::TAG_SOURCE_LOCATOR => []]);
    }

    private function registerMemberProviders(ContainerBuilder $container): void
    {
        $container->register('worse_reflection.member_provider.docblock', function (Container $container) {
            return new DocblockMemberProvider();
        }, [ self::TAG_MEMBER_PROVIDER => []]);
    }

    private function registerDiagnosticProviders(ContainerBuilder $container): void
    {
        $container->register(MissingMethodProvider::class, function (Container $container) {
            return new MissingMethodProvider();
        }, [ self::TAG_DIAGNOSTIC_PROVIDER => []]);
        $container->register(MissingDocblockReturnTypeProvider::class, function (Container $container) {
            return new MissingDocblockReturnTypeProvider();
        }, [ self::TAG_DIAGNOSTIC_PROVIDER => []]);
        $container->register(AssignmentToMissingPropertyProvider::class, function (Container $container) {
            return new AssignmentToMissingPropertyProvider();
        }, [ self::TAG_DIAGNOSTIC_PROVIDER => []]);
        $container->register(MissingReturnTypeProvider::class, function (Container $container) {
            return new MissingReturnTypeProvider();
        }, [ self::TAG_DIAGNOSTIC_PROVIDER => []]);
        $container->register(UnresolvableNameProvider::class, function (Container $container) {
            return new UnresolvableNameProvider($container->getParameter(self::PARAM_IMPORT_GLOBALS));
        }, [ self::TAG_DIAGNOSTIC_PROVIDER => []]);
        $container->register(UnusedImportProvider::class, function (Container $container) {
            return new UnusedImportProvider();
        }, [ self::TAG_DIAGNOSTIC_PROVIDER => []]);
    }
}
