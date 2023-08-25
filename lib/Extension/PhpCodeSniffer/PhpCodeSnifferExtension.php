<?php

namespace Phpactor\Extension\PhpCodeSniffer;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\OptionalExtension;
use Phpactor\Diff\RangesForDiff;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\Extension\PhpCodeSniffer\Formatter\PhpCodeSnifferFormatter;
use Phpactor\Extension\PhpCodeSniffer\LspCommand\FormatCommand;
use Phpactor\Extension\PhpCodeSniffer\Model\PhpCodeSnifferProcess;
use Phpactor\Extension\PhpCodeSniffer\Provider\PhpCodeSnifferDiagnosticsProvider;
use Phpactor\Extension\LanguageServer\Container\DiagnosticProviderTag;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\MapResolver\Resolver;

class PhpCodeSnifferExtension implements OptionalExtension
{
    public const PARAM_PHP_CODE_SNIFFER_BIN = 'php_code_sniffer.bin';
    public const PARAM_ENV = 'php_code_sniffer.env';
    public const PARAM_SHOW_DIAGNOSTICS = 'php_code_sniffer.show_diagnostics';
    public const PARAM_ENABLED = 'php_code_sniffer.enabled';

    public function load(ContainerBuilder $container): void
    {
        $container->register(
            PhpCodeSnifferProcess::class,
            function (Container $container) {
                $path = $container->get(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER)->resolve($container->parameter(self::PARAM_PHP_CODE_SNIFFER_BIN)->string());

                return new PhpCodeSnifferProcess(
                    $path,
                    LoggingExtension::channelLogger($container, 'phpcs'),
                    $container->parameter(self::PARAM_ENV)->value(),
                );
            }
        );

        $container->register(PhpCodeSnifferFormatter::class, function (Container $container) {
            return new PhpCodeSnifferFormatter(
                $container->get(PhpCodeSnifferProcess::class)
            );
        }, [
            LanguageServerExtension::TAG_FORMATTER => []
        ]);

        $container->register(PhpCodeSnifferDiagnosticsProvider::class, function (Container $container) {
            return new PhpCodeSnifferDiagnosticsProvider(
                $container->get(PhpCodeSnifferProcess::class),
                $container->parameter(self::PARAM_SHOW_DIAGNOSTICS)->bool(),
                new RangesForDiff(),
                LoggingExtension::channelLogger($container, 'phpcs'),
            );
        }, [
            LanguageServerExtension::TAG_DIAGNOSTICS_PROVIDER => DiagnosticProviderTag::create('phpcs'),
            LanguageServerExtension::TAG_CODE_ACTION_PROVIDER => []
        ]);

        $container->register(FormatCommand::class, function (Container $container) {
            return new FormatCommand(
                $container->get(PhpCodeSnifferProcess::class),
                $container->get(ClientApi::class),
                $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE),
                LoggingExtension::channelLogger($container, 'phpcs')
            );
        }, [
            LanguageServerExtension::TAG_COMMAND => [
                'name' => 'php_code_sniffer.fix'
            ],
        ]);
    }

    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_PHP_CODE_SNIFFER_BIN => '%project_root%/vendor/bin/phpcs',
            self::PARAM_ENV => [
                'XDEBUG_MODE' => 'off',
            ],
            self::PARAM_SHOW_DIAGNOSTICS => true,
        ]);

        $schema->setDescriptions([
            self::PARAM_PHP_CODE_SNIFFER_BIN => 'Path to the phpcs executable',
            self::PARAM_ENV => 'Environment for PHP_CodeSniffer (e.g. to set XDEBUG_MODE)',
            self::PARAM_SHOW_DIAGNOSTICS => 'Whether PHP_CodeSniffer diagnostics are shown'
        ]);
    }

    public function name(): string
    {
        return 'php_code_sniffer';
    }
}
