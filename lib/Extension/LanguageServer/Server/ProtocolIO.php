<?php

namespace Phpactor\Extension\LanguageServer\Server;

interface ProtocolIO
{
    public function initialize(): void;

    public function readHeaders(): string;

    public function send(string $response): void;

    public function readPayload(int $length): string;

    public function terminate();
}
