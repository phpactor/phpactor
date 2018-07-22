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
        $process = $this->phpactor('lsp:serve', $payload, true);

        while (empty($response)) {
            $response = $process->getOutput();
        }

        $process->stop();

        $this->assertContains('Content-Length', $response);
        $this->assertContains('Server has not been initialized', $response);
    }
}
