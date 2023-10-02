<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\OptionalExtension;
use Phpactor\Diff\RangesForDiff;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\Extension\LanguageServerPhpCsFixer\Formatter\PhpCsFixerFormatter;
use Phpactor\Extension\LanguageServerPhpCsFixer\LspCommand\FormatCommand;
use Phpactor\Extension\LanguageServerPhpCsFixer\Model\PhpCsFixerProcess;
use Phpactor\Extension\LanguageServerPhpCsFixer\Provider\PhpCsFixerDiagnosticsProvider;
use Phpactor\Extension\LanguageServer\Container\DiagnosticProviderTag;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\MapResolver\Resolver;

class LanguageServerPhpCsFixerExtension implements OptionalExtension
{
    public const PARAM_PHP_CS_FIXER_BIN = 'language_server_php_cs_fixer.bin';
    public const PARAM_ENV = 'language_server_php_cs_fixer.env';
    public const PARAM_SHOW_DIAGNOSTICS = 'language_server_php_cs_fixer.show_diagnostics';
    public const PARAM_CONFIG = 'language_server_php_cs_fixer.config';
    public const PARAM_ENABLED = 'language_server_php_cs_fixer.enabled';

    public function load(ContainerBuilder $container): void
    {
        $container->register(
            PhpCsFixerProcess::class,
            function (Container $container) {
                $path = $container->get(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER)->resolve($container->parameter(self::PARAM_PHP_CS_FIXER_BIN)->string());

                $configPath = null;
                if ($container->parameter(self::PARAM_CONFIG)->value()) {
                    $configPath = $container->get(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER)->resolve($container->parameter(self::PARAM_CONFIG)->string());
                }

                return new PhpCsFixerProcess(
                    $path,
                    LoggingExtension::channelLogger($container, 'php-cs-fixer'),
                    $container->parameter(self::PARAM_ENV)->value(),
                    $configPath
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
                new RangesForDiff(),
                $container->parameter(self::PARAM_SHOW_DIAGNOSTICS)->bool(),
                LoggingExtension::channelLogger($container, 'php-cs-fixer'),
            );
        }, [
            LanguageServerExtension::TAG_DIAGNOSTICS_PROVIDER => DiagnosticProviderTag::create('php-cs-fixer'),
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
            self::PARAM_ENV => [
                'XDEBUG_MODE' => 'off',
                'PHP_CS_FIXER_IGNORE_ENV' => true,
            ],
            self::PARAM_SHOW_DIAGNOSTICS => true,
            self::PARAM_CONFIG => null,
        ]);

        $schema->setDescriptions([
            self::PARAM_PHP_CS_FIXER_BIN => 'Path to the php-cs-fixer executable',
            self::PARAM_ENV => 'Environment for PHP CS Fixer (e.g. to set PHP_CS_FIXER_IGNORE_ENV)',
            self::PARAM_SHOW_DIAGNOSTICS => 'Whether PHP CS Fixer diagnostics are shown',
            self::PARAM_CONFIG => 'Set custom config'
        ]);
    }

    public function name(): string
    {
        return 'language_server_php_cs_fixer';
    }
}
