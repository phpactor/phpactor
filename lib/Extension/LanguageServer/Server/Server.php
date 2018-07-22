<?php declare(ticks=1);

namespace Phpactor\Extension\LanguageServer\Server;

use Closure;
use Exception;
use InvalidArgumentException;
use Phpactor\Extension\LanguageServer\Exception\TerminateServer;
use Phpactor\Extension\LanguageServer\Server\ProtocolIO\StdIO;
use Phpactor\Extension\LanguageServer\Server\ProtocolIO\Tcp;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Server
{
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var ProtocolIO
     */
    private $protocolIO;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Dispatcher $dispatcher,
        ProtocolIO $protocolIO,
        LoggerInterface $logger
    )
    {
        $this->dispatcher = $dispatcher;
        $this->protocolIO = $protocolIO;
        $this->logger = $logger;
    }

    public function serve(): void
    {
        $this->registerSignalHandlers();
        set_time_limit(0);

        $this->protocolIO->initialize();

        while (true) {
            try {
                $this->dispatchRequest();
            } catch (TerminateServer $exception) {
                break;
            } catch (Exception $exception) {
                $this->logger->critical($exception->getMessage());
            }
        }
    }

    private function dispatchRequest(): void
    {
        $rawHeaders = $this->protocolIO->readHeaders();
        $headers = $this->parseHeaders($rawHeaders);

        if (!isset($headers['Content-Length'])) {
            throw new RuntimeException(sprintf(
                'Could not read Content-Length header, raw request: %s',
                $rawHeaders
            ));
        }

        // add two because we read up until the first \r or \n, but the
        // delimtier is \r\n, and then there is an additional \n to remove
        $length = $headers['Content-Length'] + 2;
        $rawRequest = trim($this->protocolIO->readPayload($length));
        $request = json_decode($rawRequest, true);

        if (null === $request) {
            throw new RuntimeException(sprintf(
                'Could not decode JSON request: "%s"',$rawRequest
            ));
        }

        $response = array_filter((array) $this->dispatcher->dispatch($request));
        $response['request'] = $request;
        $response = json_encode($response);

        $contentLength = mb_strlen($response);
        $response = "Content-Length:{$contentLength}\r\n\r\n{$response}";

        $this->protocolIO->send($response);
    }

    private function parseHeaders(string $rawHeaders)
    {
        $headers = [];
        $headerLines = explode(PHP_EOL, $rawHeaders);
        foreach ($headerLines as $headerLine) {
            $headerName = substr($headerLine, 0, strpos($headerLine, ':'));
            $headerValue = trim(substr($headerLine, stripos($headerLine, ':') + 1));
            $headers[$headerName] = $headerValue;
        }

        return $headers;
    }

    private function registerSignalHandlers()
    {
        pcntl_signal(SIGINT, [$this, 'shutdown']);
        pcntl_signal(SIGTERM, [$this, 'shutdown']);
    }

    public function shutdown()
    {
        $this->logger->info('Shutting down');
        $this->protocolIO->terminate();

        throw new TerminateServer();
    }
}
