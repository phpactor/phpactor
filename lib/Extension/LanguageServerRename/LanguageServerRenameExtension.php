<?php

namespace Phpactor\Extension\LanguageServerRename;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\LanguageServerRename\Handler\FileRenameHandler;
use Phpactor\Extension\LanguageServerRename\Model\FileRenamer;
use Phpactor\Extension\LanguageServerRename\Model\Renamer\ChainRenamer;
use Phpactor\Extension\LanguageServerRename\Handler\RenameHandler;
use Phpactor\Extension\LanguageServerRename\Model\Renamer;
use Phpactor\Extension\LanguageServerRename\Util\LocatedTextEditConverter;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\MapResolver\Resolver;
use Phpactor\TextDocument\TextDocumentLocator;

class LanguageServerRenameExtension implements Extension
{
    public const TAG_RENAMER = 'language_server_rename.renamer';
    /**

     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container): void
    {
        $container->register(Renamer::class, function (Container $container) {
            return new ChainRenamer(array_map(function (string $serviceId) use ($container) {
                return $container->get($serviceId);
            }, array_keys($container->getServiceIdsForTag(self::TAG_RENAMER))));
        });

        $container->register(RenameHandler::class, function (Container $container) {
            return new RenameHandler(
                $container->get(LocatedTextEditConverter::class),
                $container->get(TextDocumentLocator::class),
                $container->get(Renamer::class),
                $container->get(ClientApi::class)
            );
        }, [
            LanguageServerExtension::TAG_METHOD_HANDLER => []
        ]);

        $container->register(FileRenameHandler::class, function (Container $container) {
            return new FileRenameHandler(
                $container->get(FileRenamer::class),
                $container->get(LocatedTextEditConverter::class),
            );
        }, [
            LanguageServerExtension::TAG_METHOD_HANDLER => []
        ]);

        $container->register(LocatedTextEditConverter::class, function (Container $container) {
            return new LocatedTextEditConverter(
                $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE),
                $container->get(TextDocumentLocator::class),
            );
        });
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema): void
    {
    }
}
