<?php

namespace Phpactor\Container;

use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use Phpactor\Application\Transformer;
use Phpactor\CodeTransform\Adapter\TolerantParser\Transformer\CompleteConstructor;
use Phpactor\CodeTransform\Adapter\WorseReflection\Transformer\ImplementContracts;
use Phpactor\CodeTransform\CodeTransform;
use Phpactor\CodeTransform\Domain\Editor;
use Phpactor\CodeTransform\Domain\Generators;
use Phpactor\CodeTransform\Domain\Transformers;
use Phpactor\UserInterface\Console\Command\ClassNewCommand;
use Phpactor\UserInterface\Console\Command\ClassTransformCommand;
use Phpactor\Application\ClassSearch;
use Phpactor\CodeTransform\Adapter\Native\GenerateNew\ClassGenerator;
use Phpactor\CodeBuilder\Adapter\Twig\TwigRenderer;
use Phpactor\Application\ClassNew;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Phpactor\CodeBuilder\Adapter\Twig\TwigExtension;
use Twig\Loader\ChainLoader;

class CodeTransformExtension implements ExtensionInterface
{
    public function getDefaultConfig()
    {
        return [
            'new_class_variants' => [
            ],
            'template_paths' => [],
        ];
    }

    public function load(Container $container)
    {
        $this->registerConsole($container);
        $this->registerTransformers($container);
        $this->registerGenerators($container);
        $this->registerApplication($container);
        $this->registerRenderer($container);
    }

    private function registerApplication(Container $container)
    {
        $container->register('application.transform', function (Container $container) {
            return new Transformer($container->get('code_transform.transform'));
        });

        $container->register('application.class_new', function (Container $container) {
            return new ClassNew(
                $container->get('application.helper.class_file_normalizer'),
                $container->get('code_transform.new_class_generators')
            );
        });
    }

    private function registerConsole(Container $container)
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
    }

    private function registerTransformers(Container $container)
    {
        $container->register('code_transform.transform', function (Container $container) {
            $transformers = [];
            foreach ($container->getServiceIdsForTag('code_transform.transformer') as $serviceId => $attrs) {
                $transformers[$attrs['name']] = $container->get($serviceId);
            }

            return CodeTransform::fromTransformers(Transformers::fromArray($transformers));
        });

        $container->register('code_transform.editor', function (Container $container) {
            return new Editor($container->getParameter('indentation'));
        });

        $container->register('code_transform.transformer.complete_constructor', function (Container $container) {
            return new CompleteConstructor(
                null,
                $container->get('code_transform.editor')
            );
        }, [ 'code_transform.transformer' => [ 'name' => 'complete_constructor' ]]);

        $container->register('code_transform.transformer.implement_contracts', function (Container $container) {
            return new ImplementContracts(
                $container->get('reflection.reflector'),
                $container->get('code_transform.editor')
            );
        }, [ 'code_transform.transformer' => [ 'name' => 'implement_contracts' ]]);
    }

    private function registerGenerators(Container $container)
    {
        $container->register('code_transform.new_class_generators', function (Container $container) {
            $generators = [
                'default' => new ClassGenerator($container->get('code_transform.renderer')),
            ];
            foreach ($container->getParameter('new_class_variants') as $variantName => $variant) {
                $generators[$variantName] = new ClassGenerator($container->get('code_transform.renderer'), $variant);
            }

            return Generators::fromArray($generators);
        });
    }

    private function registerRenderer(Container $container)
    {
        $container->register('code_transform.twig_loader', function (Container $container) {
            $loaders = [];
            $loaders[] = new FilesystemLoader(__DIR__ . '/../../vendor/phpactor/code-builder/templates');

            foreach ($container->getParameter('template_paths') as $templatePath) {
                $loaders[] = new FilesystemLoader($templatePath);
            }

            return new ChainLoader($loaders);
        });

        $container->register('code_transform.renderer', function (Container $container) {
            $twig = new Environment($container->get('code_transform.twig_loader'), [
                'strict_variables' => true,
            ]);
            $renderer = new TwigRenderer($twig);
            $twig->addExtension(new TwigExtension($renderer, $container->getParameter('indentation')));

            return $renderer;
        });
    }
}
