<?php

namespace Phpactor\Extension\LanguageServer\Server\ProtocolIO;

use Generator;
use Phpactor\Extension\LanguageServer\Server\ProtocolIO;
use Phpactor\Extension\LanguageServer\Server\StdOut;

class Tcp implements ProtocolIO
{
    /**
     * @var string
     */
    private $address;

    /**
     * @var int
     */
    private $port;

    private $socket;

    private $socketResource;

    /**
     * @var StdOut
     */
    private $stdOut;

    public function __construct(string $address, int $port, StdOut $stdOut)
    {
        $this->address = $address;
        $this->port = $port;
        $this->stdOut = $stdOut;
    }

    public function initialize(): void
    {
        $this->socket = $this->createSocket();
        $this->bindAndListenToSocket($this->socket);
    }

    public function readHeaders(): string
    {
        $this->socketResource = $this->waitForSocketResource($this->socket);
        $buffer = socket_read($this->socketResource, 2048, PHP_NORMAL_READ);

        if (false === $buffer) {
            throw new RuntimeException(sprintf(
                'Could not read from socket: %s',
                socket_strerror(socket_last_error($this->socketResource))
            ));
        }

        socket_read($this->socketResource, 2048, PHP_NORMAL_READ);

        return $buffer;
    }

    public function send(string $response): void
    {
        socket_write($this->socketResource, $response, mb_strlen($response));
    }

    public function readPayload(int $length): string
    {
        $read = socket_read($this->socketResource, $length, PHP_BINARY_READ);
        socket_close($this->socketResource);

        return $read;
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

        $this->stdOut->writeln(sprintf(
            'PID: %s Listening on %s:%s',
            getmypid(),
            $this->address,
            $this->port
        ));
    }

    public function terminate()
    {
        $this->stdOut->writeln('Shutting down');
        socket_close($this->socket);
    }

}
