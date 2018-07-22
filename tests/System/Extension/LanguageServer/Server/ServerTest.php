<?php

namespace Phpactor\Tests\System\Extension\LanguageServer\Server;

use Phpactor\Tests\System\SystemTestCase;

class ServerTest extends SystemTestCase
{
    public function testIntitialize()
    {
        $process = $this->phpactor('lsp:serve', null ,true);
        usleep(500000);

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_connect($socket, '127.0.0.1', 8383);
        socket_write($socket , <<<EOT
Content-Length: 1234\r\n
\r\n
{
	"jsonrpc": "2.0",
	"id": 1,
	"method": "test",
	"params": {}
}
EOT
    );

        $response = socket_read($socket, 2048);
        $process->stop();

        $this->assertEquals(<<<EOT
Content-Length: 119\r\n
\r\n
{"json_rpc": "2.0", "id": 1}
EOT
        ,
        trim($response));
        socket_close($socket);
    }
}
