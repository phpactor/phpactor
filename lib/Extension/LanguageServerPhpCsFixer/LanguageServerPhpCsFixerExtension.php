<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\LanguageServerPhpCsFixer\Formatter\PhpCsFixerFormatter;
use Phpactor\Extension\LanguageServerPhpCsFixer\Handler\FormattingHandler;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\MapResolver\Resolver;
use Phpactor\TextDocument\TextDocumentLocator;

class LanguageServerPhpCsFixerExtension implements Extension
{
    public const PARAM_PHP_CS_FIXER_ENABLED = 'language_server_phpcsfixer.enabled';
    public const PARAM_PHP_CS_FIXER_BIN = 'language_server_phpcsfixer.bin';

    public function load(ContainerBuilder $container): void
    {
        $container->register(PhpCsFixerFormatter::class, function (Container $container) {
            return new PhpCsFixerFormatter(
            );
        });
        $container->register(FormattingHandler::class, function (Container $container) {
            return new FormattingHandler(
                $container->get(PhpCsFixerFormatter::class),
                $container->get(TextDocumentLocator::class)
            );
        }, [
            LanguageServerExtension::TAG_METHOD_HANDLER => [
            ],
        ]);
    }


    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_PHP_CS_FIXER_ENABLED => false,
            self::PARAM_PHP_CS_FIXER_BIN => '%project_root%/vendor/bin/phpcsfixer',
        ]);
        $schema->setDescriptions([
            self::PARAM_PHP_CS_FIXER_ENABLED => 'Enable PHPStan diagnostics',
            self::PARAM_PHP_CS_FIXER_BIN => 'Path to the PHPStan executable',
        ]);
    }
}
