<?php

namespace Phpactor\Extension\LanguageServer;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\LanguageServerProtocol\ClientCapabilities;
use Phpactor\LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher\DeferredResponseWatcher;
use Phpactor\LanguageServer\Core\Server\RpcClient;
use Phpactor\LanguageServer\Core\Server\RpcClient\JsonRpcClient;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageTransmitter;
use Phpactor\LanguageServer\WorkDoneProgress\MessageProgressNotifier;
use Phpactor\LanguageServer\WorkDoneProgress\ProgressNotifier;
use Phpactor\LanguageServer\WorkDoneProgress\WorkDoneProgressNotifier;
use Phpactor\MapResolver\Resolver;

class LanguageServerSessionExtension implements Extension
{
    public function __construct(
        private MessageTransmitter $transmitter,
        private InitializeParams $initializeParams
    ) {
    }


    public function load(ContainerBuilder $container): void
    {
        $container->register(ClientCapabilities::class, function (Container $container) {
            return $this->initializeParams->capabilities;
        });

        $container->register(InitializeParams::class, function (Container $container) {
            return $this->initializeParams;
        });

        $container->register(MessageTransmitter::class, function (Container $container) {
            return $this->transmitter;
        });

        $container->register(ResponseWatcher::class, function (Container $container) {
            return new DeferredResponseWatcher();
        });

        $container->register(ClientApi::class, function (Container $container) {
            return new ClientApi($container->get(RpcClient::class));
        });

        $container->register(RpcClient::class, function (Container $container) {
            return new JsonRpcClient($this->transmitter, $container->get(ResponseWatcher::class));
        });

        $container->register(ProgressNotifier::class, function (Container $container) {
            $capabilities = $container->get(ClientCapabilities::class);
            if ($capabilities?->window?->workDoneProgress ?? false) {
                return new WorkDoneProgressNotifier($container->get(ClientApi::class));
            }

            return new MessageProgressNotifier($container->get(ClientApi::class));
        });
    }


    public function configure(Resolver $schema): void
    {
    }
}
