<?php

namespace Phpactor\Extension\ComposerInspector;

use Phpactor\ComposerInspector\ComposerInspector;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\FilePathResolver\PathResolver;
use Phpactor\MapResolver\Resolver;

class ComposerInspectorExtension implements Extension
{
    public function load(ContainerBuilder $container): void
    {
        $container->register(ComposerInspector::class, function (Container $container) {
            $path = $container->expect(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER, PathResolver::class)->resolve('%project_root%/composer.lock');
            return new ComposerInspector($path);
        });
    }

    public function configure(Resolver $schema): void
    {
    }
}
