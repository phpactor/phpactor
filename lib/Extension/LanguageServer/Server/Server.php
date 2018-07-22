<?php declare(ticks=1);

namespace Phpactor\Extension\LanguageServer\Server;

use Closure;
use InvalidArgumentException;
use Phpactor\Extension\LanguageServer\Server\IO\Tcp;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Server
{
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var Closure
     */
    private $infoMessageCallback;

    /**
     * @var Tcp
     */
    private $io;

    public function __construct(
        Dispatcher $dispatcher,
        string $address,
        string $port,
        Closure $infoMessageCallback
    )
    {
        $this->dispatcher = $dispatcher;
        $this->infoMessageCallback = $infoMessageCallback;
        $this->io = new Tcp($address, $port, new EchoStdOut());
    }

    public function serve()
    {
        set_time_limit(0);

        $this->io->initialize();

        while (true) {
            $this->dispatchRequest();
        }
    }

    private function dispatchRequest()
    {
        $rawHeaders = $this->io->readHeaders();
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
        $request = trim($this->io->readPayload($length));
        $request = json_decode($request, true);
        $response = json_encode($this->dispatcher->dispatch($request));
        $this->io->send($response);

        return $response;
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
}
