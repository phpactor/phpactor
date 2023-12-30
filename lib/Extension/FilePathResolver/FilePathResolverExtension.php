<?php

namespace Phpactor\Extension\FilePathResolver;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\FilePathResolver\CachingPathResolver;
use Phpactor\FilePathResolver\Expander\ValueExpander;
use Phpactor\FilePathResolver\Expander\Xdg\SuffixExpanderDecorator;
use Phpactor\FilePathResolver\Expander\Xdg\XdgCacheExpander;
use Phpactor\FilePathResolver\Expander\Xdg\XdgConfigExpander;
use Phpactor\FilePathResolver\Expander\Xdg\XdgDataExpander;
use Phpactor\FilePathResolver\Expanders;
use Phpactor\FilePathResolver\Filter\CanonicalizingPathFilter;
use Phpactor\FilePathResolver\Filter\TokenExpandingFilter;
use Phpactor\FilePathResolver\FilteringPathResolver;
use Phpactor\FilePathResolver\LoggingPathResolver;
use Phpactor\MapResolver\Resolver;
use Phpactor\TextDocument\TextDocumentUri;
use Psr\Log\LogLevel;
use RuntimeException;
use Phpactor\FilePathResolver\Expander;

class FilePathResolverExtension implements Extension
{
    const SERVICE_FILE_PATH_RESOLVER = 'file_path_resolver.resolver';
    const SERVICE_EXPANDERS = 'file_path_resolver.expanders';
    const TAG_FILTER = 'file_path_resolver.filter';
    const TAG_EXPANDER = 'file_path_resolver.expander';
    const PARAM_PROJECT_ROOT = 'file_path_resolver.project_root';
    const PARAM_APP_NAME = 'file_path_resolver.app_name';
    const PARAM_ENABLE_CACHE = 'file_path_resolver.enable_cache';
    const PARAM_ENABLE_LOGGING = 'file_path_resolver.enable_logging';
    const PARAM_APPLICATION_ROOT = 'file_path_resolver.application_root';
    const LOG_CHANNEL = 'FPR';


    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_PROJECT_ROOT => getcwd(),
            self::PARAM_APP_NAME => 'phpactor',
            self::PARAM_APPLICATION_ROOT => null,
            self::PARAM_ENABLE_CACHE => true,
            self::PARAM_ENABLE_LOGGING => true,
        ]);
    }


    public function load(ContainerBuilder $container): void
    {
        $this->registerPathResolver($container);
        $this->registerFilters($container);
    }

    public static function calculateProjectId(string $projectRoot): string
    {
        if (empty($projectRoot)) {
            throw new RuntimeException(
                'Project root must be a non-empty string'
            );
        }

        return sprintf(
            '%s-%s',
            basename($projectRoot),
            substr(md5(TextDocumentUri::fromString($projectRoot)->path()), 0, 6)
        );
    }

    private function registerPathResolver(ContainerBuilder $container): void
    {
        $container->register(self::SERVICE_FILE_PATH_RESOLVER, function (Container $container) {
            $filters = [];
            foreach (array_keys($container->getServiceIdsForTag(self::TAG_FILTER)) as $serviceId) {
                $filters[] = $container->get($serviceId);
            }

            $resolver = new FilteringPathResolver($filters);

            if ($container->parameter(self::PARAM_ENABLE_CACHE)->bool()) {
                $resolver = new CachingPathResolver($resolver);
            }

            if ($container->parameter(self::PARAM_ENABLE_LOGGING)->bool()) {
                $resolver = new LoggingPathResolver(
                    $resolver,
                    LoggingExtension::channelLogger($container, self::LOG_CHANNEL),
                    LogLevel::DEBUG
                );
            }

            return $resolver;
        });
    }

    private function registerFilters(ContainerBuilder $container): void
    {
        $container->register('file_path_resolver.filter.canonicalizing', function () {
            return new CanonicalizingPathFilter();
        }, [ self::TAG_FILTER => [] ]);

        $container->register('file_path_resolver.filter.token_expanding', function (Container $container) {
            return new TokenExpandingFilter($container->expect(self::SERVICE_EXPANDERS, Expanders::class));
        }, [ self::TAG_FILTER => [] ]);

        $container->register(self::SERVICE_EXPANDERS, function (Container $container) {
            $suffix = DIRECTORY_SEPARATOR . $container->getParameter(self::PARAM_APP_NAME);

            $projectRoot = $container->parameter(self::PARAM_PROJECT_ROOT)->string();
            $expanders = [
                new ValueExpander('project_id', self::calculateProjectId($projectRoot)),
                new ValueExpander('project_root', $projectRoot),
                new SuffixExpanderDecorator(new XdgCacheExpander('cache'), $suffix),
                new SuffixExpanderDecorator(new XdgConfigExpander('config'), $suffix),
                new SuffixExpanderDecorator(new XdgDataExpander('data'), $suffix),
            ];

            /** @var string|null $applicationRoot */
            $applicationRoot = $container->getParameter(self::PARAM_APPLICATION_ROOT);
            if (null !== $applicationRoot) {
                $expanders[] = new ValueExpander('application_root', $applicationRoot);
            }

            foreach (array_keys($container->getServiceIdsForTag(self::TAG_EXPANDER)) as $serviceId) {
                $expanders[] = $container->expect($serviceId, Expander::class);
            }

            return new Expanders($expanders);
        });
    }
}
