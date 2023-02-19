<?php

namespace Phpactor\Extension\LanguageServerCodeTransform;

use Phpactor\CodeTransform\Domain\Helper\MissingMethodFinder;
use Phpactor\CodeTransform\Domain\Refactor\ReplaceQualifierWithImport;
use Phpactor\CodeTransform\Domain\Refactor\ExtractConstant;
use Phpactor\CodeTransform\Domain\Refactor\ExtractExpression;
use Phpactor\CodeTransform\Domain\Refactor\ExtractMethod;
use Phpactor\CodeTransform\Domain\Refactor\FillObject;
use Phpactor\CodeTransform\Domain\Refactor\GenerateConstructor;
use Phpactor\CodeTransform\Domain\Refactor\GenerateDecorator;
use Phpactor\CodeTransform\Domain\Refactor\GenerateMethod;
use Phpactor\CodeTransform\Domain\Refactor\ImportName;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\ClassToFile\ClassToFileExtension;
use Phpactor\Extension\CodeTransform\CodeTransformExtension;
use Phpactor\Extension\LanguageServerBridge\Converter\WorkspaceEditConverter;
use Phpactor\Extension\LanguageServerCodeTransform\CodeAction\CreateClassProvider;
use Phpactor\Extension\LanguageServerCodeTransform\CodeAction\CreateUnresolvableClassProvider;
use Phpactor\Extension\LanguageServerCodeTransform\CodeAction\ExtractConstantProvider;
use Phpactor\Extension\LanguageServerCodeTransform\CodeAction\ExtractExpressionProvider;
use Phpactor\Extension\LanguageServerCodeTransform\CodeAction\ExtractMethodProvider;
use Phpactor\Extension\LanguageServerCodeTransform\CodeAction\FillObjectProvider;
use Phpactor\Extension\LanguageServerCodeTransform\CodeAction\GenerateConstructorProvider;
use Phpactor\Extension\LanguageServerCodeTransform\CodeAction\GenerateDecoratorProvider;
use Phpactor\Extension\LanguageServerCodeTransform\CodeAction\GenerateMethodProvider;
use Phpactor\Extension\LanguageServerCodeTransform\CodeAction\PropertyAccessGeneratorProvider;
use Phpactor\Extension\LanguageServerCodeTransform\CodeAction\ImportNameProvider;
use Phpactor\Extension\LanguageServerCodeTransform\CodeAction\ReplaceQualifierWithImportProvider;
use Phpactor\Extension\LanguageServerCodeTransform\CodeAction\TransformerCodeActionPovider;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\CreateClassCommand;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\ReplaceQualifierWithImportCommand;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\ExtractConstantCommand;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\ExtractExpressionCommand;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\ExtractMethodCommand;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\PropertyAccessGeneratorCommand;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\GenerateDecoratorCommand;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\GenerateMethodCommand;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\ImportAllUnresolvedNamesCommand;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\ImportNameCommand;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\TransformCommand;
use Phpactor\Extension\LanguageServerCodeTransform\Model\NameImport\CandidateFinder;
use Phpactor\Extension\LanguageServerCodeTransform\Model\NameImport\NameImporter;
use Phpactor\Extension\LanguageServer\Container\DiagnosticProviderTag;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\Indexer\Model\SearchClient;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\MapResolver\Resolver;
use Phpactor\TextDocument\TextDocumentLocator;

class LanguageServerCodeTransformExtension implements Extension
{
    public const PARAM_REPORT_NON_EXISTING_NAMES = 'language_server_code_transform.import_name.report_non_existing_names';

    public function load(ContainerBuilder $container): void
    {
        $this->registerCommands($container);
        $this->registerCodeActions($container);
    }


    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_REPORT_NON_EXISTING_NAMES => true,
        ]);
        $schema->setDescriptions([
            self::PARAM_REPORT_NON_EXISTING_NAMES => 'Show an error if a diagnostic name cannot be resolved - can produce false positives',
        ]);
    }

    private function registerCommands(ContainerBuilder $container): void
    {
        $container->register(NameImporter::class, function (Container $container) {
            return new NameImporter($container->get(ImportName::class));
        });
        $container->register(ImportNameCommand::class, function (Container $container) {
            return new ImportNameCommand(
                $container->get(NameImporter::class),
                $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE),
                $container->get(ClientApi::class)
            );
        }, [
            LanguageServerExtension::TAG_COMMAND => [
                'name' => ImportNameCommand::NAME
            ],
        ]);

        $container->register(TransformCommand::class, function (Container $container) {
            return new TransformCommand(
                $container->get(ClientApi::class),
                $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE),
                $container->get('code_transform.transformers')
            );
        }, [
            LanguageServerExtension::TAG_COMMAND => [
                'name' => TransformCommand::NAME
            ],
        ]);

        $container->register(CreateClassCommand::class, function (Container $container) {
            return new CreateClassCommand(
                $container->get(ClientApi::class),
                $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE),
                $container->get(CodeTransformExtension::SERVICE_CLASS_GENERATORS),
                $container->get(ClassToFileExtension::SERVICE_CONVERTER)
            );
        }, [
            LanguageServerExtension::TAG_COMMAND => [
                'name' => CreateClassCommand::NAME
            ],
        ]);

        $container->register(GenerateMethodCommand::class, function (Container $container) {
            return new GenerateMethodCommand(
                $container->get(ClientApi::class),
                $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE),
                $container->get(GenerateMethod::class),
                $container->get(TextDocumentLocator::class)
            );
        }, [
            LanguageServerExtension::TAG_COMMAND => [
                'name' => GenerateMethodCommand::NAME
            ],
        ]);

        $container->register(ExtractMethodCommand::class, function (Container $container) {
            return new ExtractMethodCommand(
                $container->get(ClientApi::class),
                $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE),
                $container->get(ExtractMethod::class)
            );
        }, [
            LanguageServerExtension::TAG_COMMAND => [
                'name' => ExtractMethodCommand::NAME
        ],
        ]);

        $container->register(ReplaceQualifierWithImportCommand::class, function (Container $container) {
            return new ReplaceQualifierWithImportCommand(
                $container->get(ClientApi::class),
                $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE),
                $container->get(ReplaceQualifierWithImport::class)
            );
        }, [
            LanguageServerExtension::TAG_COMMAND => [
                'name' => ReplaceQualifierWithImportCommand::NAME
            ],
        ]);

        $container->register(ExtractConstantCommand::class, function (Container $container) {
            return new ExtractConstantCommand(
                $container->get(ClientApi::class),
                $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE),
                $container->get(ExtractConstant::class)
            );
        }, [
            LanguageServerExtension::TAG_COMMAND => [
                'name' => ExtractConstantCommand::NAME
            ],
        ]);
        $container->register('language_server_code_transform.generate_accessors_command', function (Container $container) {
            return new PropertyAccessGeneratorCommand(
                $container->get(ClientApi::class),
                $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE),
                $container->get('code_transform.generate_accessor'),
                'Generate accessors'
            );
        }, [
            LanguageServerExtension::TAG_COMMAND => [
                'name' => 'generate_accessors'
            ],
        ]);

        $container->register('language_server_code_transform.generate_mutators_command', function (Container $container) {
            return new PropertyAccessGeneratorCommand(
                $container->get(ClientApi::class),
                $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE),
                $container->get('code_transform.generate_mutator'),
                'Generate mutators'
            );
        }, [
            LanguageServerExtension::TAG_COMMAND => [
                'name' => 'generate_mutators'
            ],
        ]);

        $container->register(ImportAllUnresolvedNamesCommand::class, function (Container $container) {
            return new ImportAllUnresolvedNamesCommand(
                $container->get(CandidateFinder::class),
                $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE),
                $container->get(ImportNameCommand::class),
                $container->get(ClientApi::class)
            );
        }, [
            LanguageServerExtension::TAG_COMMAND => [
                'name' => ImportAllUnresolvedNamesCommand::NAME
            ],
        ]);

        $container->register(ExtractExpressionCommand::class, function (Container $container) {
            return new ExtractExpressionCommand(
                $container->get(ClientApi::class),
                $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE),
                $container->get(ExtractExpression::class)
            );
        }, [
            LanguageServerExtension::TAG_COMMAND => [
                'name' => ExtractExpressionCommand::NAME
            ],
        ]);

        $container->register(GenerateDecoratorCommand::class, function (Container $container) {
            return new GenerateDecoratorCommand(
                $container->get(ClientApi::class),
                $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE),
                $container->get(GenerateDecorator::class)
            );
        }, [
            LanguageServerExtension::TAG_COMMAND => [
                'name' => GenerateDecoratorCommand::NAME
            ],
        ]);
    }

    private function registerCodeActions(ContainerBuilder $container): void
    {
        $container->register(CandidateFinder::class, function (Container $container) {
            return new CandidateFinder(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get(SearchClient::class),
            );
        });

        $container->register(ReplaceQualifierWithImportProvider::class, function (Container $container) {
            return new ReplaceQualifierWithImportProvider(
                $container->get(ReplaceQualifierWithImport::class)
            );
        }, [
            LanguageServerExtension::TAG_CODE_ACTION_PROVIDER => [],
        ]);

        $container->register(ImportNameProvider::class, function (Container $container) {
            return new ImportNameProvider(
                $container->get(CandidateFinder::class),
                $container->getParameter(self::PARAM_REPORT_NON_EXISTING_NAMES)
            );
        }, [
            LanguageServerExtension::TAG_CODE_ACTION_PROVIDER => [],
        ]);

        $container->register(TransformerCodeActionPovider::class.'promote_constructor_private', function (Container $container) {
            return new TransformerCodeActionPovider(
                $container->get('code_transform.transformers'),
                'promote_constructor',
                'Promote Constructor (private)'
            );
        }, [
            LanguageServerExtension::TAG_CODE_ACTION_PROVIDER => []
        ]);

        $container->register(TransformerCodeActionPovider::class.'promote_constructor_public', function (Container $container) {
            return new TransformerCodeActionPovider(
                $container->get('code_transform.transformers'),
                'promote_constructor_public',
                'Promote Constructor (public)'
            );
        }, [
            LanguageServerExtension::TAG_CODE_ACTION_PROVIDER => []
        ]);

        $container->register(TransformerCodeActionPovider::class.'complete_constructor_private', function (Container $container) {
            return new TransformerCodeActionPovider(
                $container->get('code_transform.transformers'),
                'complete_constructor',
                'Complete Constructor (private)'
            );
        }, [
            LanguageServerExtension::TAG_CODE_ACTION_PROVIDER => []
        ]);

        $container->register(TransformerCodeActionPovider::class.'complete_constructor_public', function (Container $container) {
            return new TransformerCodeActionPovider(
                $container->get('code_transform.transformers'),
                'complete_constructor_public',
                'Complete Constructor (public)'
            );
        }, [
            LanguageServerExtension::TAG_CODE_ACTION_PROVIDER => []
        ]);

        $container->register(CreateClassProvider::class, function (Container $container) {
            return new CreateClassProvider(
                $container->get(CodeTransformExtension::SERVICE_CLASS_GENERATORS),
                $container->get('worse_reflection.tolerant_parser')
            );
        }, [
            LanguageServerExtension::TAG_DIAGNOSTICS_PROVIDER => DiagnosticProviderTag::create('create-class', outsource: true),
            LanguageServerExtension::TAG_CODE_ACTION_PROVIDER => []
        ]);

        $container->register(CreateUnresolvableClassProvider::class, function (Container $container) {
            return new CreateUnresolvableClassProvider(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get(CodeTransformExtension::SERVICE_CLASS_GENERATORS),
                $container->get(ClassToFileExtension::SERVICE_CONVERTER)
            );
        }, [
            LanguageServerExtension::TAG_CODE_ACTION_PROVIDER => []
        ]);

        $container->register(TransformerCodeActionPovider::class.'add_missing_properties', function (Container $container) {
            return new TransformerCodeActionPovider(
                $container->get('code_transform.transformers'),
                'add_missing_properties',
                'Add missing properties'
            );
        }, [
            LanguageServerExtension::TAG_CODE_ACTION_PROVIDER => []
        ]);

        $container->register(TransformerCodeActionPovider::class.'implement_contracts', function (Container $container) {
            return new TransformerCodeActionPovider(
                $container->get('code_transform.transformers'),
                'implement_contracts',
                'Implement contracts'
            );
        }, [
            LanguageServerExtension::TAG_DIAGNOSTICS_PROVIDER => DiagnosticProviderTag::create('implement-contracts', outsource: true),
            LanguageServerExtension::TAG_CODE_ACTION_PROVIDER => []
        ]);

        $container->register(TransformerCodeActionPovider::class.'fix_namespace_class_name', function (Container $container) {
            return new TransformerCodeActionPovider(
                $container->get('code_transform.transformers'),
                'fix_namespace_class_name',
                'Fix PSR namespace and class name'
            );
        }, [
            LanguageServerExtension::TAG_DIAGNOSTICS_PROVIDER => DiagnosticProviderTag::create('transformer', true),
            LanguageServerExtension::TAG_CODE_ACTION_PROVIDER => []
        ]);

        $container->register(TransformerCodeActionPovider::class.'add_missing_docblocks_return', function (Container $container) {
            return new TransformerCodeActionPovider(
                $container->get('code_transform.transformers'),
                'add_missing_docblocks_return',
                'Add missing @return tags'
            );
        }, [
            LanguageServerExtension::TAG_CODE_ACTION_PROVIDER => []
        ]);
        $container->register(TransformerCodeActionPovider::class.'add_missing_params', function (Container $container) {
            return new TransformerCodeActionPovider(
                $container->get('code_transform.transformers'),
                'add_missing_params',
                'Add missing @param tags'
            );
        }, [
            LanguageServerExtension::TAG_CODE_ACTION_PROVIDER => []
        ]);

        $container->register(TransformerCodeActionPovider::class.'add_missing_return_types', function (Container $container) {
            return new TransformerCodeActionPovider(
                $container->get('code_transform.transformers'),
                'add_missing_return_types',
                'Add missing return types'
            );
        }, [
            LanguageServerExtension::TAG_CODE_ACTION_PROVIDER => []
        ]);

        $container->register(TransformerCodeActionPovider::class.'remove_unused_imports', function (Container $container) {
            return new TransformerCodeActionPovider(
                $container->get('code_transform.transformers'),
                'remove_unused_imports',
                'Remove unused imports'
            );
        }, [
            LanguageServerExtension::TAG_CODE_ACTION_PROVIDER => []
        ]);

        $container->register(GenerateMethodProvider::class, function (Container $container) {
            return new GenerateMethodProvider(
                $container->get(MissingMethodFinder::class)
            );
        }, [
            LanguageServerExtension::TAG_CODE_ACTION_PROVIDER => []
        ]);

        $container->register(ExtractMethodProvider::class, function (Container $container) {
            return new ExtractMethodProvider(
                $container->get(ExtractMethod::class)
            );
        }, [
            LanguageServerExtension::TAG_CODE_ACTION_PROVIDER => []
        ]);
        $container->register(ExtractConstantProvider::class, function (Container $container) {
            return new ExtractConstantProvider(
                $container->get(ExtractConstant::class)
            );
        }, [
            LanguageServerExtension::TAG_CODE_ACTION_PROVIDER => []
        ]);

        $container->register('language_server_code_transform.generate_accessors_provider', function (Container $container) {
            return new PropertyAccessGeneratorProvider(
                'quickfix.generate_accessors',
                'generate_accessors',
                'accessor',
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR)
            );
        }, [
            LanguageServerExtension::TAG_CODE_ACTION_PROVIDER => []
        ]);

        $container->register('language_server_code_transform.generate_mutators_provider', function (Container $container) {
            return new PropertyAccessGeneratorProvider(
                'quickfix.generate_mutators',
                'generate_mutators',
                'mutator',
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR)
            );
        }, [
            LanguageServerExtension::TAG_CODE_ACTION_PROVIDER => []
        ]);

        $container->register(ExtractExpressionProvider::class, function (Container $container) {
            return new ExtractExpressionProvider(
                $container->get(ExtractExpression::class)
            );
        }, [
            LanguageServerExtension::TAG_CODE_ACTION_PROVIDER => []
        ]);

        $container->register(FillObjectProvider::class, function (Container $container) {
            return new FillObjectProvider(
                $container->get(FillObject::class)
            );
        }, [
            LanguageServerExtension::TAG_CODE_ACTION_PROVIDER => []
        ]);

        $container->register(GenerateConstructorProvider::class, function (Container $container) {
            return new GenerateConstructorProvider(
                $container->get(GenerateConstructor::class),
                $container->get(WorkspaceEditConverter::class),
            );
        }, [
            LanguageServerExtension::TAG_CODE_ACTION_PROVIDER => []
        ]);

        $container->register(GenerateDecoratorProvider::class, function (Container $container) {
            return new GenerateDecoratorProvider(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR)
            );
        }, [
            LanguageServerExtension::TAG_CODE_ACTION_PROVIDER => []
        ]);
    }
}
