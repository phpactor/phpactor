<?php

namespace Phpactor\Extension\LanguageServerRename\Tests\Extension;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\LanguageServerRename\LanguageServerRenameExtension;
use Phpactor\Extension\LanguageServerRename\Model\FileRenamer;
use Phpactor\Extension\LanguageServerRename\Model\FileRenamer\TestFileRenamer;
use Phpactor\Extension\LanguageServerRename\Model\Renamer\InMemoryRenamer;
use Phpactor\MapResolver\Resolver;
use Phpactor\TextDocument\ByteOffsetRange;

class TestExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container): void
    {
        $container->register(InMemoryRenamer::class, function (Container $container) {
            return new InMemoryRenamer(
                $container->getParameter('range'),
                $container->getParameter('results'),
            );
        }, [
            LanguageServerRenameExtension::TAG_RENAMER => []
        ]);

        $container->register(FileRenamer::class, function (Container $container) {
            return new TestFileRenamer(
            );
        }, [
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            'range' => ByteOffsetRange::fromInts(0, 10),
            'results' => [],
        ]);
    }
}
