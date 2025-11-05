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
use Phpactor\FilePathResolver\PathResolver;
use Phpactor\LanguageServerProtocol\DiagnosticSeverity;
use Phpactor\MapResolver\Resolver;
use InvalidArgumentException;

class LanguageServerPhpstanExtension implements OptionalExtension
{
    public const PARAM_PHPSTAN_BIN = 'language_server_phpstan.bin';
    public const PARAM_LEVEL = 'language_server_phpstan.level';
    public const PARAM_CONFIG = 'language_server_phpstan.config';
    public const PARAM_MEM_LIMIT = 'language_server_phpstan.mem_limit';
    public const PARAM_ENABLED = 'language_server_phpstan.enabled';
    public const PARAM_TMP_FILE_DISABLED = 'language_server_phpstan.tmp_file_disabled';
    public const PARAM_EDITOR_MODE = 'language_server_phpstan.editor_mode';
    public const PARAM_SEVERITY = 'language_server_phpstan.severity';

    public function load(ContainerBuilder $container): void
    {
        $container->register(
            PhpstanDiagnosticProvider::class,
            function (Container $container) {
                return new PhpstanDiagnosticProvider(
                    $container->get(Linter::class)
                );
            },
            [
                LanguageServerExtension::TAG_DIAGNOSTICS_PROVIDER => DiagnosticProviderTag::create('phpstan'),
            ]
        );

        $container->register(
            Linter::class,
            function (Container $container) {
                if ($container->parameter(self::PARAM_EDITOR_MODE)->bool()
                    && $container->parameter(self::PARAM_TMP_FILE_DISABLED)->value()
                ) {
                    throw new InvalidArgumentException('You can not disable temp files with editor mode enabled');
                }

                return new PhpstanLinter(
                    $container->get(PhpstanProcess::class),
                    $container->parameter(self::PARAM_TMP_FILE_DISABLED)->value() ?  $container->parameter(self::PARAM_TMP_FILE_DISABLED)->bool() : false,
                    $container->parameter(self::PARAM_EDITOR_MODE)->bool(),
                );
            }
        );

        $container->register(
            PhpstanProcess::class,
            function (Container $container) {
                $pathResolver = $container->expect(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER, PathResolver::class);

                $binPath = $pathResolver->resolve($container->parameter(self::PARAM_PHPSTAN_BIN)->string());

                $root = $pathResolver->resolve('%project_root%');

                $configPath = null;
                if ($container->parameter(self::PARAM_CONFIG)->value()) {
                    $configPath = $pathResolver->resolve($container->parameter(self::PARAM_CONFIG)->string());
                }

                $phpstanConfig =  new PhpstanConfig(
                    $binPath,
                    $container->parameter(self::PARAM_SEVERITY)->value() ? $container->parameter(self::PARAM_SEVERITY)->int() : DiagnosticSeverity::ERROR,
                    $container->parameter(self::PARAM_LEVEL)->value() ?  $container->parameter(self::PARAM_LEVEL)->string() : null,
                    $configPath,
                    $container->parameter(self::PARAM_MEM_LIMIT)->value() ?  $container->parameter(self::PARAM_MEM_LIMIT)->string() : null,
                );

                return new PhpstanProcess(
                    $root,
                    $phpstanConfig,
                    LoggingExtension::channelLogger($container, 'phpstan'),
                );
            }
        );
    }


    public function configure(Resolver $schema): void
    {
        $schema->setDefaults(
            [
            self::PARAM_PHPSTAN_BIN => '%project_root%/vendor/bin/phpstan',
            self::PARAM_LEVEL => null,
            self::PARAM_CONFIG => null,
            self::PARAM_MEM_LIMIT => null,
            self::PARAM_TMP_FILE_DISABLED => false,
            self::PARAM_EDITOR_MODE => false,
            self::PARAM_SEVERITY => DiagnosticSeverity::ERROR,
            ]
        );
        $schema->setDescriptions(
            [
            self::PARAM_PHPSTAN_BIN => 'Path to the PHPStan executable',
            self::PARAM_LEVEL => 'Override the PHPStan level',
            self::PARAM_CONFIG => 'Override the PHPStan configuration file',
            self::PARAM_MEM_LIMIT => 'Override the PHPStan memory limit',
            self::PARAM_TMP_FILE_DISABLED => 'Disable the use of temporary files when.'
                . ' This prevents as-you-type diagnostics, but ensures paths in phpstan config are respected.'
                    . ' See https://github.com/phpactor/phpactor/issues/2763',
            self::PARAM_EDITOR_MODE => 'Use the editor mode of Phpstan https://phpstan.org/user-guide/editor-mode'
                . ' (Requires phpstan 2.14 or higher)',
            self::PARAM_SEVERITY => 'Severity at which PHPStan diagnostics should be reported. Ranges from 1 (error) to 4 (hint).'
            ]
        );
    }

    public function name(): string
    {
        return 'language_server_phpstan';
    }
}
