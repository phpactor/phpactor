<?php

namespace Phpactor\Extension\LanguageServerDiagnostics;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\LanguageServerDiagnostics\Model\PhpLinter;
use Phpactor\Extension\LanguageServerDiagnostics\Provider\PhpLintDiagnosticProvider;
use Phpactor\Extension\LanguageServer\Container\DiagnosticProviderTag;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\MapResolver\Resolver;
use Phpactor\TextDocument\TextDocumentLocator;

class LanguageServerDiagnosticsExtension implements Extension
{
    public function load(ContainerBuilder $container): void
    {
        $container->register(PhpLintDiagnosticProvider::class, function (Container $container) {
            return new PhpLintDiagnosticProvider(
                new PhpLinter(PHP_BINARY),
                $container->get(TextDocumentLocator::class)
            );
        }, [
            LanguageServerExtension::TAG_DIAGNOSTICS_PROVIDER => DiagnosticProviderTag::create('php')
        ]);
    }


    public function configure(Resolver $schema): void
    {
    }
}
