<?php

namespace Phpactor\Extension\LanguageServer\Server;

use RuntimeException;

class Server
{
    /**
     * @var string
     */
    private $address;

    /**
     * @var string
     */
    private $port;

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    public function __construct(Dispatcher $dispatcher, string $address, string $port)
    {
        $this->address = $address;
        $this->port = $port;
        $this->dispatcher = $dispatcher;
    }

    public function serve()
    {
        set_time_limit(0);
        ob_implicit_flush();

        $socket = $this->createSocket();
        $this->bindAndListenToSocket($socket);

        do {
            $socketResource = $this->waitForSocketResource($socket);

            do {
                $this->dispatchRequest($socketResource);

            } while (true);
            socket_close($socketResource);
        } while (true);

        socket_close($socket);
    }

    private function createSocket()
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        
        if (false === $socket) {
            throw new RuntimeException(sprintf(
                'Could not create socket: %s',
                socket_strerror(socket_last_error())
            ));
        }

        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_set_option($socket, SOL_SOCKET, SO_REUSEPORT, 1);

        return $socket;
    }

    private function bindAndListenToSocket($socket)
    {
        if (socket_bind($socket, $this->address, $this->port) === false) {
            throw new RuntimeException(sprintf(
                'Could not bind socket: %s',
                socket_strerror(socket_last_error($socket))
            ));
        }

        if (socket_listen($socket, 5) === false) {
            throw new RuntimeException(sprintf(
                'Could not listen on socket: %s',
                socket_strerror(socket_last_error($socket))
            ));
        }
    }

    private function waitForSocketResource($socket)
    {
        $socketResource = socket_accept($socket);

        if (false === $socketResource) {
            throw new RuntimeException(sprintf(
                'Could not accept socket: %s',
                socket_strerror(socket_last_error($socket))
            ));
        }

        return $socketResource;
    }

    private function dispatchRequest($socketResource)
    {
        $buffer = socket_read($socketResource, 2048, PHP_NORMAL_READ);

        if (false === $buffer) {
            throw new RuntimeException(sprintf(
                'Could not read from socket: %s',
                socket_strerror(socket_last_error($socketResource))
            ));
        }

        $headers = $this->parseHeaders($buffer);

        if (!isset($headers['Content-Length'])) {
            throw new RuntimeException(sprintf(
                'Could not read Content-Length header, raw request: %s',
                $buffer
            ));
        }
        socket_read($socketResource, 2048, PHP_NORMAL_READ);

        // add two because we read up until the first \r or \n, but the
        // delimtier is \r\n, and then there is an additional \n to remove
        $length = $headers['Content-Length'] + 2;
        $buffer = trim(socket_read($socketResource, $length, PHP_BINARY_READ));
        $request = json_decode($buffer, true);

        $response = json_encode($this->dispatcher->dispatch($request['method'], $request['params']));
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

    public function address(): string
    {
        return $this->address;
    }

    public function port(): string
    {
        return $this->port;
    }
}
