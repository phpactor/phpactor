<?php

namespace Phpactor\Extension\Php;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Php\Model\ChainResolver;
use Phpactor\Extension\Php\Model\ComposerPhpVersionResolver;
use Phpactor\Extension\Php\Model\ConstantPhpVersionResolver;
use Phpactor\Extension\Php\Model\PhpVersionResolver;
use Phpactor\Extension\Php\Model\RuntimePhpVersionResolver;
use Phpactor\FilePathResolverExtension\FilePathResolverExtension;
use Phpactor\MapResolver\Resolver;

class PhpExtension implements Extension
{
    const PARAM_VERSION = 'php.version';

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container): void
    {
        $container->register(PhpVersionResolver::class, function (Container $container) {
            $pathResolver = $container->get(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER);
            $composerPath = $pathResolver->resolve('%project_root%/composer.json');

            return new ChainResolver(
                new ConstantPhpVersionResolver($container->getParameter(self::PARAM_VERSION)),
                new ComposerPhpVersionResolver($composerPath),
                new RuntimePhpVersionResolver()
            );
        });
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_VERSION => null
        ]);
        $schema->setDescriptions([
            self::PARAM_VERSION => <<<'EOT'
                Consider this value to be the project\'s version of PHP (e.g. `7.4`). If omitted
                it will check `composer.json` (by the configured platform then the PHP requirement) before
                falling back to the PHP version of the current process.
                EOT
        ]);
    }
}
