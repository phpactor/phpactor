<?php

namespace Phpactor\Extension\LanguageServerMago;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\OptionalExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\Extension\LanguageServer\Container\DiagnosticProviderTag;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Extension\LanguageServerMago\Model\Linter\MagoLinter;
use Phpactor\Extension\LanguageServerMago\Model\MagoConfig;
use Phpactor\Extension\LanguageServerMago\Model\MagoProcess;
use Phpactor\Extension\LanguageServerMago\Provider\MagoDiagnosticProvider;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\FilePathResolver\PathResolver;
use Phpactor\MapResolver\Resolver;

class LanguageServerMagoExtension implements OptionalExtension
{
    public const PARAM_BIN = 'language_server_mago.bin';
    public const PARAM_CONFIG = 'language_server_mago.config';
    public const PARAM_TIMEOUT = 'language_server_mago.timeout';
    public const PARAM_ENABLED = 'language_server_mago.enabled';
    public const PARAM_ANALYZE_ENABLED = 'language_server_mago.analyze.enabled';
    public const PARAM_LINT_ENABLED = 'language_server_mago.lint.enabled';
    private const SERVICE_ANALYZE_PROVIDER = 'language_server_mago.provider.analyze';
    private const SERVICE_LINT_PROVIDER = 'language_server_mago.provider.lint';

    public function load(ContainerBuilder $container): void
    {
        $container->register(MagoProcess::class, function (Container $container) {
            $pathResolver = $container->expect(
                FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER,
                PathResolver::class
            );

            $bin = $pathResolver->resolve($container->parameter(self::PARAM_BIN)->string());
            $root = $pathResolver->resolve('%project_root%');

            $config = null;
            if ($container->parameter(self::PARAM_CONFIG)->value()) {
                $config = $pathResolver->resolve($container->parameter(self::PARAM_CONFIG)->string());
            }

            return new MagoProcess(
                $root,
                new MagoConfig($bin, $container->parameter(self::PARAM_TIMEOUT)->int(), $config),
                LoggingExtension::channelLogger($container, 'mago'),
            );
        });

        $this->registerProvider(
            $container,
            self::SERVICE_ANALYZE_PROVIDER,
            'analyze',
            'mago',
            self::PARAM_ANALYZE_ENABLED,
        );
        $this->registerProvider(
            $container,
            self::SERVICE_LINT_PROVIDER,
            'lint',
            'mago-lint',
            self::PARAM_LINT_ENABLED,
        );
    }

    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_BIN => '%project_root%/vendor/bin/mago',
            self::PARAM_CONFIG => null,
            self::PARAM_TIMEOUT => 10000,
            self::PARAM_ANALYZE_ENABLED => true,
            self::PARAM_LINT_ENABLED => true,
        ]);
        $schema->setDescriptions([
            self::PARAM_BIN => 'Path to the Mago executable',
            self::PARAM_CONFIG => 'Override the Mago configuration file (mago.toml)',
            self::PARAM_TIMEOUT => 'Maximum time in milliseconds to wait for a Mago run',
            self::PARAM_ANALYZE_ENABLED => 'Show diagnostics from `mago analyze` (static analysis)',
            self::PARAM_LINT_ENABLED => 'Show diagnostics from `mago lint` (style and code smells)',
        ]);
    }

    public function name(): string
    {
        return 'language_server_mago';
    }

    private function registerProvider(
        ContainerBuilder $container,
        string $serviceId,
        string $subcommand,
        string $source,
        string $enabledParam,
    ): void {
        $container->register($serviceId, function (Container $container) use ($subcommand, $source, $enabledParam) {
            $root = $container->expect(
                FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER,
                PathResolver::class
            )->resolve('%project_root%');

            return new MagoDiagnosticProvider(
                new MagoLinter($container->get(MagoProcess::class), $root, $subcommand, $source),
                $source,
                $container->parameter($enabledParam)->bool(),
            );
        }, [
            LanguageServerExtension::TAG_DIAGNOSTICS_PROVIDER => DiagnosticProviderTag::create($source),
        ]);
    }
}
