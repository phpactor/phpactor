<?php

namespace Phpactor\Tests\System\Extension\LanguageServer\Server;

use Phpactor\Tests\System\SystemTestCase;

class ServerTest extends SystemTestCase
{
    public function testIntitialize()
    {
        $process = $this->phpactor('lsp:serve', null ,true);
        while (false === $process->isRunning()) {
        }
            usleep(500000);

        $socket = fsockopen('127.0.0.1', '8383');
        fwrite($socket, <<<EOT
Content-Length: 1234\r\n
\r\n
{
	"jsonrpc": "2.0",
	"id": 1,
	"method": "textDocument/didOpen",
	"params": {
	}
}
EOT
        );
        fclose($socket);

        $process->stop();
        var_dump($process->getOutput());
        var_dump($process->getErrorOutput());
    }
}
