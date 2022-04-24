<?php

namespace Phpactor\Extension\LanguageServerHover;

use Phpactor\CodeBuilder\Domain\TemplatePathResolver\PhpVersionPathResolver;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Extension\LanguageServerHover\Twig\TwigFunctions;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\ObjectRenderer\ObjectRendererBuilder;
use Phpactor\Extension\Php\Model\PhpVersionResolver;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Extension\LanguageServerHover\Handler\HoverHandler;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;
use Twig\Environment;

class LanguageServerHoverExtension implements Extension
{
    public const PARAM_TEMPLATE_PATHS = 'language_server_hover.template_paths';
    private const SERVICE_MARKDOWN_RENDERER = 'language_server_completion.object_renderer.markdown';

    
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
        $container->register('language_server_completion.handler.hover', function (Container $container) {
            return new HoverHandler(
                $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE),
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get(self::SERVICE_MARKDOWN_RENDERER)
            );
        }, [ LanguageServerExtension::TAG_METHOD_HANDLER => []]);

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
