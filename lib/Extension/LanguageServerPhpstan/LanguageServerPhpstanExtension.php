<?php

namespace Phpactor\Extension\LanguageServerPhpstan;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\OptionalExtension;
use Phpactor\Extension\LanguageServerPhpstan\Model\Linter;
use Phpactor\Extension\LanguageServerPhpstan\Model\Linter\PhpstanLinter;
use Phpactor\Extension\LanguageServerPhpstan\Model\PhpstanConfig;
use Phpactor\Extension\LanguageServerPhpstan\Model\PhpstanProcess;
use Phpactor\Extension\LanguageServerPhpstan\Provider\PhpstanDiagnosticProvider;
use Phpactor\Extension\LanguageServer\Container\DiagnosticProviderTag;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\MapResolver\Resolver;

class LanguageServerPhpstanExtension implements OptionalExtension
{
    public const PARAM_PHPSTAN_BIN = 'language_server_phpstan.bin';
    public const PARAM_LEVEL = 'language_server_phpstan.level';
    public const PARAM_CONFIG = 'language_server_phpstan.config';
    public const PARAM_MEM_LIMIT = 'language_server_phpstan.mem_limit';
    public const PARAM_ENABLED = 'language_server_phpstan.enabled';

    public function load(ContainerBuilder $container): void
    {
        $container->register(PhpstanDiagnosticProvider::class, function (Container $container) {
            return new PhpstanDiagnosticProvider(
                $container->get(Linter::class)
            );
        }, [
            LanguageServerExtension::TAG_DIAGNOSTICS_PROVIDER=> DiagnosticProviderTag::create('phpstan'),
        ]);

        $container->register(Linter::class, function (Container $container) {
            return new PhpstanLinter($container->get(PhpstanProcess::class));
        });

        $container->register(PhpstanProcess::class, function (Container $container) {
            $binPath = $container->get(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER)->resolve($container->getParameter(self::PARAM_PHPSTAN_BIN));
            $root = $container->get(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER)->resolve('%project_root%');
            $configPath = $container->getParameter(self::PARAM_CONFIG) ? $container->get(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER)->resolve($container->getParameter(self::PARAM_CONFIG)) : null;

            return new PhpstanProcess(
                $root,
                new PhpstanConfig($binPath, $container->getParameter(self::PARAM_LEVEL), $configPath, $container->getParameter(self::PARAM_MEM_LIMIT)),
                LoggingExtension::channelLogger($container, 'phpstan'),
            );
        });
    }


    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_PHPSTAN_BIN => '%project_root%/vendor/bin/phpstan',
            self::PARAM_LEVEL => null,
            self::PARAM_CONFIG => null,
            self::PARAM_MEM_LIMIT => null,
        ]);
        $schema->setDescriptions([
            self::PARAM_PHPSTAN_BIN => 'Path to the PHPStan executable',
            self::PARAM_LEVEL => 'Override the PHPStan level',
            self::PARAM_CONFIG => 'Override the PHPStan configuration file',
            self::PARAM_MEM_LIMIT => 'Override the PHPStan memory limit',
        ]);
    }

    public function name(): string
    {
        return 'language_server_phpstan';
    }
}
