<?php

namespace Phpactor\Extension\LanguageServerPsalm;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\OptionalExtension;
use Phpactor\Extension\LanguageServerPsalm\DiagnosticProvider\PsalmDiagnosticProvider;
use Phpactor\Extension\LanguageServerPsalm\Model\Linter;
use Phpactor\Extension\LanguageServerPsalm\Model\Linter\PsalmLinter;
use Phpactor\Extension\LanguageServerPsalm\Model\PsalmConfig;
use Phpactor\Extension\LanguageServerPsalm\Model\PsalmProcess;
use Phpactor\Extension\LanguageServer\Container\DiagnosticProviderTag;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\FilePathResolver\PathResolver;
use Phpactor\MapResolver\Resolver;

class LanguageServerPsalmExtension implements OptionalExtension
{
    public const PARAM_PSALM_BIN = 'language_server_psalm.bin';
    public const PARAM_PSALM_SHOW_INFO = 'language_server_psalm.show_info';
    public const PARAM_PSALM_USE_CACHE = 'language_server_psalm.use_cache';
    public const PARAM_ENABLED = 'language_server_psalm.enabled';
    public const PARAM_PSALM_ERROR_LEVEL = 'language_server_psalm.error_level';
    public const PARAM_PSALM_THREADS = 'language_server_psalm.threads';
    public const PARAM_TIMEOUT = 'language_server_psalm.timeout';
    public const PARAM_PSALM_CONFIG = 'language_server_psalm.config';

    public function load(ContainerBuilder $container): void
    {
        $container->register(PsalmDiagnosticProvider::class, function (Container $container) {
            return new PsalmDiagnosticProvider(
                $container->get(Linter::class)
            );
        }, [
            LanguageServerExtension::TAG_DIAGNOSTICS_PROVIDER => DiagnosticProviderTag::create('psalm'),
        ]);

        $container->register(Linter::class, function (Container $container) {
            return new PsalmLinter($container->get(PsalmProcess::class));
        });

        $container->register(PsalmProcess::class, function (Container $container) {
            $resolver = $container->expect(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER, PathResolver::class);
            $binPath = $resolver->resolve($container->parameter(self::PARAM_PSALM_BIN)->string());
            $configPath = $resolver->resolve($container->parameter(self::PARAM_PSALM_CONFIG)->string());
            $root = $resolver->resolve('%project_root%');
            $shouldShowInfo = $container->parameter(self::PARAM_PSALM_SHOW_INFO)->bool();
            $useCache = $container->parameter(self::PARAM_PSALM_USE_CACHE)->bool();
            $errorLevel = $container->parameter(self::PARAM_PSALM_ERROR_LEVEL)->value();
            $threads = $container->parameter(self::PARAM_PSALM_THREADS)->value();
            if (!is_null($errorLevel) && !is_int($errorLevel)) {
                $errorLevel = null;
            }
            if (!is_null($threads) && !is_int($threads)) {
                $threads = null;
            }

            return new PsalmProcess(
                cwd: $root,
                config: new PsalmConfig(
                    $binPath,
                    $shouldShowInfo,
                    $useCache,
                    $errorLevel ? (int)$errorLevel : null,
                    $threads ? (int)$threads : null,
                    $configPath === '' ? null : $configPath,
                ),
                logger: LoggingExtension::channelLogger($container, 'PSALM'),
                timeoutSeconds: $container->parameter(self::PARAM_TIMEOUT)->int(),
            );
        });
    }


    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_PSALM_BIN => '%project_root%/vendor/bin/psalm',
            self::PARAM_PSALM_CONFIG => '',
            self::PARAM_PSALM_SHOW_INFO => true,
            self::PARAM_PSALM_USE_CACHE => true,
            self::PARAM_PSALM_ERROR_LEVEL => null,
            self::PARAM_PSALM_THREADS => 1,
            self::PARAM_TIMEOUT => 15,
        ]);
        $schema->setTypes([
            self::PARAM_PSALM_BIN => 'string',
            self::PARAM_PSALM_CONFIG => 'string',
            self::PARAM_PSALM_SHOW_INFO => 'boolean',
            self::PARAM_PSALM_USE_CACHE => 'boolean',
            self::PARAM_TIMEOUT => 'integer',
            self::PARAM_PSALM_THREADS => 'integer',
        ]);
        $schema->setDescriptions([
            self::PARAM_PSALM_BIN => 'Path to psalm if different from vendor/bin/psalm',
            self::PARAM_PSALM_CONFIG => 'Path to psalm config. Like %project_root%/psalm.xml',
            self::PARAM_PSALM_SHOW_INFO => 'If infos from psalm should be displayed',
            self::PARAM_PSALM_USE_CACHE => 'If the Psalm cache should be used (see the `--no-cache` option)',
            self::PARAM_PSALM_ERROR_LEVEL => 'Override level at which Psalm should report errors (lower => more errors)',
            self::PARAM_PSALM_THREADS => 'Set the number of threads Psalm should use. Warning: NULL will use as many as possible and may crash your computer',
            self::PARAM_TIMEOUT => 'Kill the psalm process after this number of seconds',
        ]);
    }

    public function name(): string
    {
        return 'language_server_psalm';
    }
}
