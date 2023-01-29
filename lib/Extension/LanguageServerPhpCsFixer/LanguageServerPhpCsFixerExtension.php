<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\OptionalExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\Extension\LanguageServerPhpCsFixer\Formatter\PhpCsFixerFormatter;
use Phpactor\Extension\LanguageServerPhpCsFixer\LspCommand\FormatCommand;
use Phpactor\Extension\LanguageServerPhpCsFixer\Model\PhpCsFixerProcess;
use Phpactor\Extension\LanguageServerPhpCsFixer\Provider\PhpCsFixerDiagnosticsProvider;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\MapResolver\Resolver;

class LanguageServerPhpCsFixerExtension implements OptionalExtension
{
    public const PARAM_PHP_CS_FIXER_BIN = 'language_server_php_cs_fixer.bin';
    public const PARAM_ENV = 'language_server_php_cs_fixer.env';
    public const PARAM_SHOW_DIAGNOSTICS = 'language_server_php_cs_fixer.show_diagnostics';

    public function load(ContainerBuilder $container): void
    {
        $container->register(
            PhpCsFixerProcess::class,
            function (Container $container) {
                $path = $container->get(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER)->resolve($container->getParameter(self::PARAM_PHP_CS_FIXER_BIN));

                return new PhpCsFixerProcess(
                    $path,
                    LoggingExtension::channelLogger($container, 'php-cs-fixer'),
                    $container->getParameter(self::PARAM_ENV),
                );
            }
        );

        $container->register(PhpCsFixerFormatter::class, function (Container $container) {
            return new PhpCsFixerFormatter($container->get(PhpCsFixerProcess::class));
        }, [
            LanguageServerExtension::TAG_FORMATTER => []
        ]);

        $container->register(PhpCsFixerDiagnosticsProvider::class, function (Container $container) {
            return new PhpCsFixerDiagnosticsProvider(
                $container->get(PhpCsFixerProcess::class),
                $container->getParameter(self::PARAM_SHOW_DIAGNOSTICS),
                LoggingExtension::channelLogger($container, 'php-cs-fixer'),
            );
        }, [
            LanguageServerExtension::TAG_DIAGNOSTICS_PROVIDER => [
                'name' => 'php-cs-fixer'
            ],
            LanguageServerExtension::TAG_CODE_ACTION_PROVIDER => []
        ]);

        $container->register(FormatCommand::class, function (Container $container) {
            return new FormatCommand(
                $container->get(PhpCsFixerProcess::class),
                $container->get(ClientApi::class),
                $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE),
                LoggingExtension::channelLogger($container, 'php-cs-fixer')
            );
        }, [
            LanguageServerExtension::TAG_COMMAND => [
                'name' => 'php_cs_fixer.fix'
            ],
        ]);
    }

    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_PHP_CS_FIXER_BIN => '%project_root%/vendor/bin/php-cs-fixer',
            self::PARAM_ENV => [],
            self::PARAM_SHOW_DIAGNOSTICS => true,
        ]);

        $schema->setDescriptions([
            self::PARAM_PHP_CS_FIXER_BIN => 'Path to the php-cs-fixer executable',
            self::PARAM_ENV => 'Environemnt for PHP CS Fixer (e.g. to set PHP_CS_FIXER_IGNORE_ENV)',
            self::PARAM_SHOW_DIAGNOSTICS => 'Whether PHP CS Fixer diagnostics are shown'
        ]);
    }

    public function name(): string
    {
        return 'language_server_php_cs_fixer';
    }
}
