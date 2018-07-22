<?php

namespace Phpactor\Tests\Integration\Extension\LanguageServer;

use Phpactor\Extension\LanguageServer\Protocol\InitializeResult;
use Phpactor\Extension\LanguageServer\Protocol\ResponseError;

class InitializeTest extends LanguageServerTestCase
{
    public function testReturnsInitializeResult()
    {
        $response = $this->initialize();

        if (null !== $response->error) {
            $this->fail('Returned error: ' . $response->error->message . print_r($response->error->data, true));
        }

        $this->assertInstanceOf(InitializeResult::class, $response->result);
    }

    public function testReturnsErrorIfRequestMadeBeforeInitialization()
    {
        $response = $this->sendRequest([
            'method' => 'foobar',
            'params' => [],
        ]);
        $this->assertInstanceOf(ResponseError::class, $response->error);
        $this->assertEquals('Server has not been initialized', $response->error->message);
        $this->assertEquals(ResponseError::ServerNotInitialized, $response->error->code);
    }
}
