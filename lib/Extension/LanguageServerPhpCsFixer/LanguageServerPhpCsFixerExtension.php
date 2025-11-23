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
use Phpactor\Extension\LanguageServerPhpCsFixer\VersionResolver\PhpCsFixerVersionResolver;
use Phpactor\Extension\LanguageServer\Container\DiagnosticProviderTag;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\FilePathResolver\PathResolver;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\MapResolver\Resolver;
use Phpactor\VersionResolver\AggregateSemVerResolver;
use Phpactor\VersionResolver\ArbitrarySemVerResolver;
use Phpactor\VersionResolver\CachedSemVerResolver;
use Phpactor\VersionResolver\SemVersionResolver;

class LanguageServerPhpCsFixerExtension implements OptionalExtension
{
    public const SERVICE_VERSION_RESOLVER = 'php_cs_fixer.version.resolver';
    public const PARAM_PHP_CS_FIXER_BIN = 'language_server_php_cs_fixer.bin';
    public const PARAM_PHP_CS_FIXER_VERSION = 'language_server_php_cs_fixer.version';
    public const PARAM_ENV = 'language_server_php_cs_fixer.env';
    public const PARAM_SHOW_DIAGNOSTICS = 'language_server_php_cs_fixer.show_diagnostics';
    public const PARAM_CONFIG = 'language_server_php_cs_fixer.config';
    public const PARAM_ENABLED = 'language_server_php_cs_fixer.enabled';

    public function load(ContainerBuilder $container): void
    {
        $container->register(self::SERVICE_VERSION_RESOLVER, function (Container $container) {
            $pathResolver = $container->expect(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER, PathResolver::class);

            $path = $pathResolver->resolve($container->parameter(self::PARAM_PHP_CS_FIXER_BIN)->string());

            return new CachedSemVerResolver(
                new AggregateSemVerResolver(
                    new ArbitrarySemVerResolver($container->parameter(self::PARAM_PHP_CS_FIXER_VERSION)->stringOrNull()),
                    new PhpCsFixerVersionResolver($path, LoggingExtension::channelLogger($container, 'php-cs-fixer')),
                ),
            );
        });

        $container->register(
            PhpCsFixerProcess::class,
            function (Container $container) {
                $pathResolver = $container->expect(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER, PathResolver::class);

                $path = $pathResolver->resolve($container->parameter(self::PARAM_PHP_CS_FIXER_BIN)->string());

                $configPath = null;
                if ($container->parameter(self::PARAM_CONFIG)->value()) {
                    $configPath = $pathResolver->resolve($container->parameter(self::PARAM_CONFIG)->string());
                }

                return new PhpCsFixerProcess(
                    $path,
                    LoggingExtension::channelLogger($container, 'php-cs-fixer'),
                    $container->expect(self::SERVICE_VERSION_RESOLVER, SemVersionResolver::class),
                    /** @phpstan-ignore-next-line */
                    $container->parameter(self::PARAM_ENV)->value(),
                    $configPath,
                );
            },
        );

        $container->register(PhpCsFixerFormatter::class, function (Container $container) {
            return new PhpCsFixerFormatter($container->get(PhpCsFixerProcess::class));
        }, [
            LanguageServerExtension::TAG_FORMATTER => [],
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
            LanguageServerExtension::TAG_CODE_ACTION_PROVIDER => [],
        ]);

        $container->register(FormatCommand::class, function (Container $container) {
            return new FormatCommand(
                $container->get(PhpCsFixerProcess::class),
                $container->get(ClientApi::class),
                $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE),
                LoggingExtension::channelLogger($container, 'php-cs-fixer'),
            );
        }, [
            LanguageServerExtension::TAG_COMMAND => [
                'name' => 'php_cs_fixer.fix',
            ],
        ]);
    }

    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_PHP_CS_FIXER_BIN => '%project_root%/vendor/bin/php-cs-fixer',
            self::PARAM_PHP_CS_FIXER_VERSION => null,
            self::PARAM_ENV => [
                'XDEBUG_MODE' => 'off',
            ],
            self::PARAM_SHOW_DIAGNOSTICS => true,
            self::PARAM_CONFIG => null,
        ]);

        $schema->setDescriptions([
            self::PARAM_PHP_CS_FIXER_BIN => 'Path to the php-cs-fixer executable',
            self::PARAM_PHP_CS_FIXER_VERSION => 'Arbitrary version (if not provided, phpactor tries to detect it - only to run it on unsupported PHP versions)',
            self::PARAM_ENV => 'Environment for PHP CS Fixer',
            self::PARAM_SHOW_DIAGNOSTICS => 'Whether PHP CS Fixer diagnostics are shown',
            self::PARAM_CONFIG => 'Set custom PHP CS config path. Ex., %project_root%/.php-cs-fixer.php',
        ]);
    }

    public function name(): string
    {
        return 'language_server_php_cs_fixer';
    }
}
