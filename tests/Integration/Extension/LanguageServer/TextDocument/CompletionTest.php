<?php

namespace Phpactor\Tests\Integration\Extension\LanguageServer\TextDocument;

use Phpactor\Tests\Integration\Extension\LanguageServer\LanguageServerTestCase;

class CompletionTest extends LanguageServerTestCase
{

    public function testComplete()
    {
        $this->initialize();
        $response = $this->runRequests(__DIR__ . '/completion.json');
    }

    protected function runRequests(string $filename)
    {
        $json = file_get_contents($filename);
        $decoded = json_decode($json, true);

        foreach ($decoded as $request) {
            $response = $this->sendRequest($request);
        }

        return $response;
    }
}
