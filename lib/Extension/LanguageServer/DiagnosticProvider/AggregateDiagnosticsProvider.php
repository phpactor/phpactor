<?php

namespace Phpactor\Extension\LanguageServer\DiagnosticProvider;

use Amp\CancellationToken;
use Amp\Promise;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;
use Psr\Log\LoggerInterface;
use function Amp\call;
use Throwable;

class AggregateDiagnosticsProvider implements DiagnosticsProvider
{
    /**
     * @var array<DiagnosticsProvider>
     */
    private readonly array $providers;

    public function __construct(
        private readonly LoggerInterface $logger, 
        DiagnosticsProvider ...$providers
    ) {
        $this->providers = $providers;
    }

    public function provideDiagnostics(TextDocumentItem $textDocument, CancellationToken $cancel): Promise
    {
        return call(function () use ($textDocument, $cancel) {
            $diagnostics = [];
            foreach ($this->providers as $provider) {
                try {
                    $start = microtime(true);
                    $diagnostics = array_merge(
                        $diagnostics,
                        // if no code is provided in the diagnostic, set the
                        // code to be the provider name.
                        array_map(function (Diagnostic $diagnostic) use ($provider) {
                            if (null === $diagnostic->code) {
                                $diagnostic->code = $provider->name();
                            }
                            return $diagnostic;
                        }, yield $provider->provideDiagnostics($textDocument, $cancel))
                    );
                    if ($cancel->isRequested()) {
                        $this->logger->info('Diagnostics cancelled');
                        return $diagnostics;
                    }
                    $this->logger->debug(sprintf(
                        'Diagnostic finsihed in "%s" (%s)',
                        number_format(microtime(true) - $start, 2),
                        get_class($provider)
                    ));
                } catch (Throwable $throwable) {
                    $this->logger->error(sprintf(
                        'Diagnostic error from provider "%s": %s',
                        get_class($provider),
                        $throwable->getMessage()
                    ), [
                        'trace' => $throwable->getTraceAsString()
                    ]);
                }
            }

            return $diagnostics;
        });
    }

    /**
     * @return array<string>
     */
    public function names(): array
    {
        return array_map(
            fn (DiagnosticsProvider $provider): string => $provider->name(),
            $this->providers
        );
    }

    public function name(): string
    {
        return implode(', ', $this->names());
    }
}
