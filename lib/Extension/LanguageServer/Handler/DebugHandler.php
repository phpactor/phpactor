<?php

namespace Phpactor\Extension\LanguageServer\Handler;

use Amp\Promise;
use Amp\Success;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\Container\Container;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;
use Phpactor\LanguageServer\Core\Server\ServerStats;
use Phpactor\LanguageServer\Core\Service\ServiceManager;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Handler\Handler;

class DebugHandler implements Handler
{
    const METHOD_DEBUG_CONFIG = 'phpactor/debug/config';
    const METHOD_DEBUG_WORKSPACE = 'phpactor/debug/workspace';
    const METHOD_DEBUG_STATUS = 'phpactor/status';

    private Container $container;

    private ClientApi $client;

    private Workspace $workspace;

    private ServerStats $stats;

    private ServiceManager $serviceManager;

    private DiagnosticsProvider $diagnosticProvider;

    public function __construct(
        Container $container,
        ClientApi $client,
        Workspace $workspace,
        ServerStats $stats,
        ServiceManager $serviceManager,
        DiagnosticsProvider $diagnosticProvider
    ) {
        $this->container = $container;
        $this->client = $client;
        $this->workspace = $workspace;
        $this->stats = $stats;
        $this->serviceManager = $serviceManager;
        $this->diagnosticProvider = $diagnosticProvider;
    }

    
    public function methods(): array
    {
        return [
            self::METHOD_DEBUG_CONFIG => 'dumpConfig',
            self::METHOD_DEBUG_WORKSPACE => 'dumpWorkspace',
            self::METHOD_DEBUG_STATUS => 'status'
        ];
    }

    /**
     * @return Promise<null|string>
     */
    public function dumpConfig(bool $return = false): Promise
    {
        $message = [
            'Config Dump',
            '===========',
            '',
            'File Paths',
            '----------',
            '',
        ];
        $paths = [];

        foreach (
            $this->container->get(
                FilePathResolverExtension::SERVICE_EXPANDERS
            )->toArray() as $tokenName => $value
        ) {
            $message[] = sprintf('%s: %s', $tokenName, $value);
        }

        $message[] = '';
        $message[] = 'Config';
        $message[] = '------';


        $json = (string)json_encode($this->container->getParameters(), JSON_PRETTY_PRINT);
        $message[] = $json;

        if ($return) {
            return new Success($json);
        }

        $this->client->window()->logMessage()->info(implode(PHP_EOL, $message));
        return new Success(null);
    }

    /**
     * @return Promise<null>
     */
    public function dumpWorkspace(): Promise
    {
        $info = [];
        foreach ($this->workspace as $document) {
            assert($document instanceof TextDocumentItem);
            $info[] = sprintf('// %s', $document->uri);
            $info[] = '-----------------';
            $info[] = $document->text;
        }

        $this->client->window()->logMessage()->info(implode("\n", $info));

        return new Success(null);
    }

    /**
     * @return Promise<string>
     */
    public function status(): Promise
    {
        $info = [
            'Process',
            '-------',
            '',
            '  cwd:' . getcwd(),
            '  pid: ' . getmypid(),
            '  up: ' . $this->stats->uptime()->format('%ad %hh %im %ss'),
            '',
            'Server',
            '------',
            '',
            // '  connections: ' . $this->stats->connectionCount(),
            // '  requests: ' . $this->stats->requestCount(),
            '  mem: ' . number_format(memory_get_peak_usage()) . 'b',
            '  documents: ' . $this->workspace->count(),
            '  services: ' . (string)json_encode($this->serviceManager->runningServices()),
            '  diagnostics: ' . (string)$this->diagnosticProvider->name(),
            '',
            'Paths',
            '-----',
            '',
        ];
        foreach (
            $this->container->get(
                FilePathResolverExtension::SERVICE_EXPANDERS
            )->toArray() as $tokenName => $value
        ) {
            $info[] = sprintf('  %s: %s', $tokenName, $value);
        }

        return new Success(implode(PHP_EOL, $info));
    }
}
