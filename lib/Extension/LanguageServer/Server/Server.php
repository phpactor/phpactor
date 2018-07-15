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

    public function __construct(string $address, string $port)
    {
        $this->address = $address;
        $this->port = $port;
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

        $buffer = socket_read($socketResource, $headers['Content-Length'], PHP_BINARY_READ);
        
        echo $buffer;
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
