<?php

namespace Phpactor\Tests\Integration\Extension\LanguageServer;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServer\Protocol\ResponseMessage;
use Phpactor\Extension\LanguageServer\Server\Dispatcher;
use Phpactor\Tests\IntegrationTestCase;

class LanguageServerTestCase extends IntegrationTestCase
{
    public function initialize(): ResponseMessage
    {
        $request = <<<'EOT'
{                                          
    "jsonrpc": "2.0",                      
    "method": "initialize",                
    "params": {                            
        "capabilities": {                  
            "textDocument": {              
                "completion": {            
                    "completionItem": {    
                        "snippetSupport": false                                        
                    }                      
                }                          
            },                             
            "workspace": {                 
                "applyEdit": true,         
                "didChangeWatchedFiles": { 
                    "dynamicRegistration": true                                        
                }                          
            }                              
        },                                 
        "processId": 22152,                
        "rootPath": "\/home\/daniel\/www\/phpactor\/phpactor",                         
        "rootUri": "file:\/\/\/home\/daniel\/www\/phpactor\/phpactor",                 
        "trace": "off"                     
    },                                     
    "id": 10                               
}
EOT
        ;
        return $this->sendRequest($request);
    }

    public function sendRequest($request): ResponseMessage
    {
        if (is_string($request)) {
            $request = json_decode($request, true);
        }

        $dispatcher = $this->container()->get('language_server.dispatcher');
        assert($dispatcher instanceof Dispatcher);

        return $dispatcher->dispatch($request);
    }
}
