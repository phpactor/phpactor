<?php

namespace Phpactor\Extension\ObjectRenderer;

use Phpactor\CodeBuilder\Domain\TemplatePathResolver\PhpVersionPathResolver;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Extension\LanguageServerHover\Twig\TwigFunctions;
use Phpactor\Container\Extension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\Php\Model\PhpVersionResolver;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\MapResolver\Resolver;
use Twig\Environment;

class ObjectRendererExtension implements Extension
{
    public const PARAM_TEMPLATE_PATHS = 'object_renderer.template_paths.markdown';
    public const SERVICE_MARKDOWN_RENDERER = 'object_renderer.renderer.markdown';
    
    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_TEMPLATE_PATHS => [
                '%project_config%/templates/markdown',
                '%config%/templates/markdown',
            ]
        ]);

        $schema->setDescriptions([
            self::PARAM_TEMPLATE_PATHS => 'Paths in which to look for templates for hover information.'
        ]);
    }


    public function load(ContainerBuilder $container): void
    {
        $container->register(self::SERVICE_MARKDOWN_RENDERER, function (Container $container) {
            $resolver = $container->get(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER);
            $templatePaths = $container->getParameter(self::PARAM_TEMPLATE_PATHS);
            $templatePaths[] = __DIR__ . '/../../../templates/help/markdown';

            $resolvedTemplatePaths = array_map(function (string $path) use ($resolver) {
                return $resolver->resolve($path);
            }, $templatePaths);

            $phpVersion = $container->get(PhpVersionResolver::class)->resolve();
            $paths = (new PhpVersionPathResolver($phpVersion))->resolve($resolvedTemplatePaths);

            $builder = ObjectRendererBuilder::create()
                ->setLogger(LoggingExtension::channelLogger($container, 'LSP-HOVER'))
                ->enableInterfaceCandidates()
                ->enableAncestoralCandidates()
                ->configureTwig(function (Environment $env) {
                    $env = TwigFunctions::add($env);
                    return $env;
                })
                    ->renderEmptyOnNotFound();

            foreach ($paths as $path) {
                $builder = $builder->addTemplatePath($path);
            }

            return $builder->build();
        });
    }
}
