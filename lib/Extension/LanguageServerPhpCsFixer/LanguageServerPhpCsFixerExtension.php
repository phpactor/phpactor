<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\OptionalExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\Extension\LanguageServerPhpCsFixer\Formatter\PhpCsFixerFormatter;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\MapResolver\Resolver;

class LanguageServerPhpCsFixerExtension implements OptionalExtension
{
    public const PARAM_PHP_CS_FIXER_BIN = 'language_server_php_cs_fixer.bin';

    public function load(ContainerBuilder $container): void
    {
        $container->register(PhpCsFixerFormatter::class, function (Container $container) {
            $path = $container->get(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER)->resolve($container->getParameter(self::PARAM_PHP_CS_FIXER_BIN));
            return new PhpCsFixerFormatter($path);
        }, [
            LanguageServerExtension::TAG_FORMATTER => []
        ]);
    }

    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_PHP_CS_FIXER_BIN => '%project_root%/vendor/bin/php-cs-fixer',

        ]);

        $schema->setDescriptions([
            self::PARAM_PHP_CS_FIXER_BIN => 'Path to the php-cs-fixer executable',
        ]);
    }

    public function name(): string
    {
        return 'language_server_php_cs_fixer';
    }
}
