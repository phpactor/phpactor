<?php

namespace Phpactor\Extension\CodeTransform;

use Phpactor\Extension\CodeTransform\Application\Transformer;
use Phpactor\CodeTransform\Adapter\WorseReflection\Transformer\ImplementContracts;
use Phpactor\CodeTransform\CodeTransform;
use Phpactor\CodeTransform\Domain\Generators;
use Phpactor\CodeTransform\Domain\Transformers;
use Phpactor\Extension\CodeTransform\Command\ClassNewCommand;
use Phpactor\Extension\CodeTransform\Command\ClassTransformCommand;
use Phpactor\CodeTransform\Adapter\Native\GenerateNew\ClassGenerator;
use Phpactor\CodeBuilder\Adapter\Twig\TwigRenderer;
use Phpactor\Extension\CodeTransform\Application\ClassNew;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Phpactor\CodeBuilder\Adapter\Twig\TwigExtension;
use Twig\Loader\ChainLoader;
use Phpactor\CodeTransform\Adapter\WorseReflection\GenerateFromExisting\InterfaceFromExistingGenerator;
use Phpactor\Extension\CodeTransform\Command\ClassInflectCommand;
use Phpactor\Extension\CodeTransform\Application\ClassInflect;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeBuilder\Adapter\TolerantParser\TolerantUpdater;
use Phpactor\CodeBuilder\Util\TextFormat;
use Phpactor\Config\ConfigLoader;
use Phpactor\CodeTransform\Adapter\WorseReflection\Transformer\AddMissingProperties;
use Phpactor\CodeTransform\Adapter\WorseReflection\Transformer\CompleteConstructor;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseExtractConstant;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseGenerateMethod;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseGenerateAccessor;
use Phpactor\CodeTransform\Adapter\TolerantParser\Refactor\TolerantRenameVariable;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseOverrideMethod;
use Phpactor\CodeBuilder\Adapter\WorseReflection\WorseBuilderFactory;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseExtractMethod;
use Phpactor\CodeTransform\Domain\Refactor\ImportClass;
use Phpactor\CodeTransform\Adapter\TolerantParser\Refactor\TolerantImportClass;
use Phpactor\Config\Paths;
use Phpactor\Container\Extension;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Container;
use Phpactor\Container\Schema;

class CodeTransformExtension implements Extension
{
    const CLASS_NEW_VARIANTS = 'code_transform.class_new.variants';
    const TEMPLATE_PATHS = 'code_transform.template_paths';
    const INDENTATION = 'code_transform.indentation';
    const GENERATE_ACCESSOR_PREFIX = 'code_transform.refactor.generate_accessor.prefix';
    const GENERATE_ACCESSOR_UPPER_CASE_FIRST = 'code_transform.refactor.generate_accessor.upper_case_first';


    /**
     * {@inheritDoc}
     */
    public function configure(Schema $schema)
    {
        $paths = new Paths();
        $templatePaths = $paths->existingConfigPaths('templates');

        $schema->setDefaults([
            self::CLASS_NEW_VARIANTS => [],
            self::TEMPLATE_PATHS => $templatePaths,
            self::INDENTATION => '    ',
            self::GENERATE_ACCESSOR_PREFIX => '',
            self::GENERATE_ACCESSOR_UPPER_CASE_FIRST => false,
        ]);
    }

    public function load(ContainerBuilder $container)
    {
        $this->registerConsole($container);
        $this->registerTransformers($container);
        $this->registerGenerators($container);
        $this->registerApplication($container);
        $this->registerRenderer($container);
        $this->registerUpdater($container);
        $this->registerRefactorings($container);
    }

    private function registerApplication(ContainerBuilder $container)
    {
        $container->register('application.transform', function (Container $container) {
            return new Transformer(
                $container->get('code_transform.transform')
            );
        });

        $container->register('application.class_new', function (Container $container) {
            return new ClassNew(
                $container->get('application.helper.class_file_normalizer'),
                $container->get('code_transform.new_class_generators')
            );
        });

        $container->register('application.class_inflect', function (Container $container) {
            return new ClassInflect(
                $container->get('application.helper.class_file_normalizer'),
                $container->get('code_transform.from_existing_generators'),
                $container->get('monolog.logger')
            );
        });
    }

    private function registerConsole(ContainerBuilder $container)
    {
        $container->register('command.transform', function (Container $container) {
            return new ClassTransformCommand(
                $container->get('application.transform')
            );
        }, [ 'ui.console.command' => []]);

        $container->register('command.class_new', function (Container $container) {
            return new ClassNewCommand(
                $container->get('application.class_new'),
                $container->get('console.dumper_registry')
            );
        }, [ 'ui.console.command' => []]);

        $container->register('command.class_inflect', function (Container $container) {
            return new ClassInflectCommand(
                $container->get('application.class_inflect'),
                $container->get('console.dumper_registry')
            );
        }, [ 'ui.console.command' => []]);
    }

    private function registerTransformers(ContainerBuilder $container)
    {
        $container->register('code_transform.transformers', function (Container $container) {
            $transformers = [];
            foreach ($container->getServiceIdsForTag('code_transform.transformer') as $serviceId => $attrs) {
                $transformers[$attrs['name']] = $container->get($serviceId);
            }

            return Transformers::fromArray($transformers);
        });

        $container->register('code_transform.transform', function (Container $container) {
            return CodeTransform::fromTransformers($container->get('code_transform.transformers'));
        });

        $container->register('code_transform.transformer.complete_constructor', function (Container $container) {
            return new CompleteConstructor(
                $container->get('reflection.reflector'),
                $container->get('code_transform.updater')
            );
        }, [ 'code_transform.transformer' => [ 'name' => 'complete_constructor' ]]);

        $container->register('code_transform.transformer.implement_contracts', function (Container $container) {
            return new ImplementContracts(
                $container->get('reflection.reflector'),
                $container->get('code_transform.updater'),
                $container->get('code_transform.builder_factory')
            );
        }, [ 'code_transform.transformer' => [ 'name' => 'implement_contracts' ]]);

        $container->register('code_transform.transformer.add_missing_properties', function (Container $container) {
            return new AddMissingProperties(
                $container->get('reflection.reflector'),
                $container->get('code_transform.updater')
            );
        }, [ 'code_transform.transformer' => [ 'name' => 'add_missing_properties' ]]);
    }

    private function registerGenerators(ContainerBuilder $container)
    {
        $container->register('code_transform.new_class_generators', function (Container $container) {
            $generators = [
                'default' => new ClassGenerator($container->get('code_transform.renderer')),
            ];
            foreach ($container->getParameter(self::CLASS_NEW_VARIANTS) as $variantName => $variant) {
                $generators[$variantName] = new ClassGenerator($container->get('code_transform.renderer'), $variant);
            }

            return Generators::fromArray($generators);
        });

        $container->register('code_transform.from_existing_generators', function (Container $container) {
            $generators = [
                'interface' => new InterfaceFromExistingGenerator(
                    $container->get('reflection.reflector'),
                    $container->get('code_transform.renderer')
                ),
            ];

            return Generators::fromArray($generators);
        });
    }

    private function registerRenderer(ContainerBuilder $container)
    {
        $container->register('code_transform.twig_loader', function (Container $container) {
            $loaders = [];
            $loaders[] = new FilesystemLoader(__DIR__ . '/../../../vendor/phpactor/code-builder/templates');

            foreach ($container->getParameter(self::TEMPLATE_PATHS) as $templatePath) {
                $loaders[] = new FilesystemLoader($templatePath);
            }

            return new ChainLoader($loaders);
        });

        $container->register('code_transform.renderer', function (Container $container) {
            $twig = new Environment($container->get('code_transform.twig_loader'), [
                'strict_variables' => true,
            ]);
            $renderer = new TwigRenderer($twig);
            $twig->addExtension(new TwigExtension($renderer, $container->get('code_transform.text_format')));

            return $renderer;
        });

        $container->register('code_transform.text_format', function (Container $container) {
            return new TextFormat($container->getParameter(self::INDENTATION));
        });
    }

    private function registerRefactorings(ContainerBuilder $container)
    {
        $container->register('code_transform.refactor.extract_constant', function (Container $container) {
            return new WorseExtractConstant(
                $container->get('reflection.reflector'),
                $container->get('code_transform.updater')
            );
        });

        $container->register('code_transform.refactor.generate_method', function (Container $container) {
            return new WorseGenerateMethod(
                $container->get('reflection.reflector'),
                $container->get('code_transform.builder_factory'),
                $container->get('code_transform.updater')
            );
        });

        $container->register('code_transform.refactor.generate_accessor', function (Container $container) {
            return new WorseGenerateAccessor(
                $container->get('reflection.reflector'),
                $container->get('code_transform.updater'),
                $container->getParameter(self::GENERATE_ACCESSOR_PREFIX),
                $container->getParameter(self::GENERATE_ACCESSOR_UPPER_CASE_FIRST)
            );
        });

        $container->register('code_transform.refactor.rename_variable', function (Container $container) {
            return new TolerantRenameVariable();
        });

        $container->register('code_transform.refactor.override_method', function (Container $container) {
            return new WorseOverrideMethod(
                $container->get('reflection.reflector'),
                $container->get('code_transform.builder_factory'),
                $container->get('code_transform.updater')
            );
        });

        $container->register('code_transform.refactor.extract_method', function (Container $container) {
            return new WorseExtractMethod(
                $container->get('reflection.reflector'),
                $container->get('code_transform.builder_factory'),
                $container->get('code_transform.updater')
            );
        });

        $container->register('code_transform.refactor.class_import', function (Container $container) {
            return new TolerantImportClass(
                $container->get('code_transform.updater')
            );
        });
    }

    private function registerUpdater(ContainerBuilder $container)
    {
        $container->register('code_transform.updater', function (Container $container) {
            return new TolerantUpdater($container->get('code_transform.renderer'));
        });
        $container->register('code_transform.builder_factory', function (Container $container) {
            return new WorseBuilderFactory($container->get('reflection.reflector'));
        });
    }
}
