<?php

namespace Phpactor\Tests\System\Extension\LanguageServer\Server\ProtocolIO;

use Phpactor\Tests\System\SystemTestCase;
use Symfony\Component\Process\InputStream;

class StdIOTest extends SystemTestCase
{
    public function testIntitialize()
    {
        $payload = <<<EOT
Content-Length: 1234\r\n
\r\n
{
	"jsonrpc": "2.0",
	"id": 1,
	"method": "test",
	"params": {}
}
EOT;
        $process = $this->phpactor('lsp:serve', $payload);
        $response = $process->getOutput();
        $process->stop();

        $this->assertEquals(<<<EOT
Content-Length: 120\r\n
\r\n
{"id":"1","result":null,"error":{"code":-32002,"message":"Server has not been initialized","data":null},"jsonrpc":"2.0"}
EOT
        ,
        trim($response));
    }
}
