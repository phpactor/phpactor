<?php

namespace Phpactor\Extension\LanguageServer\Server\Dispatcher;

use Phpactor\Extension\LanguageServer\Protocol\ResponseMessage;
use Phpactor\Extension\LanguageServer\Server\Dispatcher;

class WriteRequestsToFileDispatcher implements Dispatcher
{
    /**
     * @var Dispatcher
     */
    private $innerDispatcher;

    public function __construct(Dispatcher $innerDispatcher, string $fileName)
    {
        $this->innerDispatcher = $innerDispatcher;
        $this->resource = fopen($fileName, 'w');
    }

    public function dispatch(array $request): ResponseMessage
    {
        fwrite($this->resource, json_encode($request, JSON_PRETTY_PRINT));

        return $this->innerDispatcher->dispatch($request);
    }

    public function __destruct()
    {
        fclose($this->resource);
    }
}
