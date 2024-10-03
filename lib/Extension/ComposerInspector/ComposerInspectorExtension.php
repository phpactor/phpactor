<?php

namespace Phpactor\Extension\ComposerInspector;

use Phpactor\ComposerInspector\ComposerInspector;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\FilePathResolver\Expander\ValueExpander;
use Phpactor\FilePathResolver\PathResolver;
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
            $path = $container->get(ComposerInspector::class)->getBinDir();
            return new ValueExpander('composer_bin_dir', $path);
        }, [ FilePathResolverExtension::TAG_EXPANDER => [] ]);
    }

    public function configure(Resolver $schema): void
    {
    }
}
