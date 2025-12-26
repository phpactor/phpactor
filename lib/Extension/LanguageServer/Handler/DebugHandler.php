<?php

namespace Phpactor\Extension\LanguageServer\Handler;

use Amp\Promise;
use Amp\Success;
use Phpactor\Extension\LanguageServer\Status\StatusProvider;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\Container\Container;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;
use Phpactor\LanguageServer\Core\Server\ServerStats;
use Phpactor\LanguageServer\Core\Service\ServiceManager;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\FilePathResolver\Expanders;

class DebugHandler implements Handler
{
    const METHOD_DEBUG_CONFIG = 'phpactor/debug/config';
    const METHOD_DEBUG_WORKSPACE = 'phpactor/debug/workspace';
    const METHOD_DEBUG_STATUS = 'phpactor/status';

    /**
     * @param StatusProvider[] $statusProviders
     */
    public function __construct(
        private readonly Container $container,
        private readonly ClientApi $client,
        private readonly Workspace $workspace,
        private readonly ServerStats $stats,
        private readonly ServiceManager $serviceManager,
        private readonly DiagnosticsProvider $diagnosticProvider,
        private readonly array $statusProviders
    ) {
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

        $this->dumpExpanders($message);

        $message[] = '';
        $message[] = 'Config';
        $message[] = '------';


        $json = (string)json_encode($this->container->getParameters(), JSON_PRETTY_PRINT);
        $message[] = $json;

        if ($return) {
            return new Success($json);
        }

        $this->client->window()->logMessage()->info(implode("\n", $message));
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

        $this->dumpExpanders($info);
        $info[] = '';

        foreach ($this->statusProviders as $provider) {
            $info[] = $provider->title();
            $info[] = str_repeat('-', mb_strlen($provider->title()));
            $info[] = '';
            foreach ($provider->provide() as $key => $value) {
                $info[] = sprintf('  %s: %s', $key, $value);
            }
        }

        return new Success(implode("\n", $info));
    }

    /**
     * @param array<string> $output
     */
    private function dumpExpanders(array &$output): void
    {
        foreach (
            $this->container->expect(
                FilePathResolverExtension::SERVICE_EXPANDERS,
                Expanders::class
            )->toArray() as $tokenName => $value
        ) {
            $output[] = sprintf('  %s: %s', $tokenName, $value);
        }
    }
}
