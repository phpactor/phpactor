<?php

namespace Phpactor\Extension\LanguageServerCompletion;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Completion\CompletionExtension;
use Phpactor\Extension\LanguageServerCodeTransform\Model\NameImport\NameImporter;
use Phpactor\Extension\LanguageServerCompletion\Handler\SignatureHelpHandler;
use Phpactor\Extension\LanguageServerCompletion\Util\SuggestionNameFormatter;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Extension\LanguageServerCompletion\Handler\CompletionHandler;
use Phpactor\LanguageServerProtocol\ClientCapabilities;
use Phpactor\MapResolver\Resolver;

class LanguageServerCompletionExtension implements Extension
{
    private const PARAM_TRIM_LEADING_DOLLAR = 'language_server_completion.trim_leading_dollar';

    public const TAG_DOCUMENT_MODIFIER = 'language_server_completion.document_modifier';


    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_TRIM_LEADING_DOLLAR => false,
        ]);
        $schema->setDescriptions([
            self::PARAM_TRIM_LEADING_DOLLAR => 'If the leading dollar should be trimmed for variable completion suggestions',
        ]);
    }


    public function load(ContainerBuilder $container): void
    {
        $this->registerHandlers($container);
    }

    private function registerHandlers(ContainerBuilder $container): void
    {
        $container->register('language_server_completion.handler.completion', function (Container $container) {
            $documentModifiers = [];

            foreach (array_keys($container->getServiceIdsForTag(self::TAG_DOCUMENT_MODIFIER)) as $serviceId) {
                $documentModifier = $container->get($serviceId);
                if (null === $documentModifier) {
                    continue;
                }
                $documentModifiers[] = $documentModifier;
            }

            return new CompletionHandler(
                $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE),
                $container->get(CompletionExtension::SERVICE_REGISTRY),
                $container->get(SuggestionNameFormatter::class),
                $container->get(NameImporter::class),
                $this->clientCapabilities($container)->textDocument->completion->completionItem['snippetSupport'] ?? false,
                false,
                $documentModifiers
            );
        }, [ LanguageServerExtension::TAG_METHOD_HANDLER => [
            'methods' => [
                'textDocument/completion'
            ]
        ]]);

        $container->register(SuggestionNameFormatter::class, function (Container $container) {
            return new SuggestionNameFormatter($container->getParameter(self::PARAM_TRIM_LEADING_DOLLAR));
        });

        $container->register('language_server_completion.handler.signature_help', function (Container $container) {
            return new SignatureHelpHandler(
                $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE),
                $container->get(CompletionExtension::SERVICE_SIGNATURE_HELPER)
            );
        }, [ LanguageServerExtension::TAG_METHOD_HANDLER => [] ]);
    }

    private function clientCapabilities(Container $container): ClientCapabilities
    {
        return $container->get(ClientCapabilities::class);
    }
}
