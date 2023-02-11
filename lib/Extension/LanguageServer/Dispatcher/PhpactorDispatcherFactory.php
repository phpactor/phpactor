<?php

namespace Phpactor\Extension\LanguageServer\Dispatcher;

use Phpactor\Container\Extension;
use Phpactor\Container\OptionalExtension;
use Phpactor\Extension\LanguageServer\LanguageServerSessionExtension;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\MiddlewareDispatcher;
use Phpactor\LanguageServer\Core\Server\Exception\ExitSession;
use Phpactor\Container\Container;
use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\LanguageServerProtocol\InitializeParams;
use Phpactor\MapResolver\ResolverErrors;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\DispatcherFactory;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageTransmitter;

class PhpactorDispatcherFactory implements DispatcherFactory
{
    public function __construct(private Container $container)
    {
    }

    public function create(MessageTransmitter $transmitter, InitializeParams $initializeParams): Dispatcher
    {
        $container = $this->createContainer($initializeParams, $transmitter);
        return $this->createContainer(
            $initializeParams,
            $transmitter
        )->get(MiddlewareDispatcher::class);
    }

    protected function createContainer(InitializeParams $params, MessageTransmitter $transmitter): Container
    {
        $container = $this->container;
        $parameters = $container->getParameters();
        $parameters[FilePathResolverExtension::PARAM_PROJECT_ROOT] = TextDocumentUri::fromString(
            $this->resolveRootUri($params)
        )->path();

        $extensionClasses = $container->getParameter(
            PhpactorContainer::PARAM_EXTENSION_CLASSES
        );

        // merge in any language-server specific configuration
        $parameters = array_merge($parameters, $container->getParameter(LanguageServerExtension::PARAM_SESSION_PARAMETERS));

        $container = $this->buildContainer(
            $extensionClasses,
            array_merge($parameters, $params->initializationOptions ?? []),
            $transmitter,
            $params
        );

        return $container;
    }
    /**
     * @param list<class-string> $extensionClasses
     * @param array<string,mixed> $parameters
     */
    private function buildContainer(
        array $extensionClasses,
        array $parameters,
        MessageTransmitter $transmitter,
        InitializeParams $params
    ): Container {
        $container = new PhpactorContainer();

        $extensions = array_map(function (string $class): Extension {
            /** @var Extension $class */
            return new $class;
        }, $extensionClasses);
        $extensions[] = new LanguageServerSessionExtension($transmitter, $params);

        $resolver = new Resolver(true);
        $resolver->setDefaults([
            PhpactorContainer::PARAM_EXTENSION_CLASSES => $extensionClasses
        ]);
        foreach ($extensions as $extension) {
            // This is duplicated in ExtensionDocumentor we should not
            // continue to add behavior like this here and should extract
            // this and other special logic.
            if ($extension instanceof OptionalExtension) {
                (function (string $key) use ($resolver): void {
                    $resolver->setDefaults([$key => false]);
                    $resolver->setTypes([$key => 'boolean']);
                })(sprintf('%s.enabled', $extension->name()));
            }
            $extension->configure($resolver);
        }

        $parameters = $resolver->resolve($parameters);

        $container->register(ResolverErrors::class, function () use ($resolver) {
            return $resolver->errors();
        });

        foreach ($extensions as $extension) {
            if ($extension instanceof OptionalExtension) {
                if (false === ($parameters[sprintf('%s.enabled', $extension->name())] ?? false)) {
                    continue;
                }
            }
            $extension->load($container);
        }

        return $container->build($parameters);
    }

    private function resolveRootUri(InitializeParams $params): string
    {
        if (null === $params->rootUri) {
            throw new ExitSession(
                'Phpactor Language Server must be initialized with a root URI, NULL provided'
            );
        }

        return $params->rootUri;
    }
}
