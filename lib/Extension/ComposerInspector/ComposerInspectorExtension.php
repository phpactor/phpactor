<?php

namespace Phpactor\Extension\ComposerInspector;

use Phpactor\ComposerInspector\ComposerInspector;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\FilePathResolver\PathResolver;
use Phpactor\FilePathResolver\Expander\CallbackExpander;
use Phpactor\MapResolver\Resolver;

class ComposerInspectorExtension implements Extension
{
    public function load(ContainerBuilder $container): void
    {
        $container->register(ComposerInspector::class, function (Container $container) {
            $pathResolver = $container->expect(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER, PathResolver::class);
            return new ComposerInspector(
                $pathResolver->resolve('%project_root%/composer.lock'),
                $pathResolver->resolve('%project_root%/composer.json'),
            );
        });

        $container->register('composer.bin_path_expander', function (Container $container) {
            return new CallbackExpander('composer_bin_dir', fn () => $container->get(ComposerInspector::class)->binDir());
        });
    }

    public function configure(Resolver $schema): void
    {
    }
}
