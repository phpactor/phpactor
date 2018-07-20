<?php

namespace Phpactor\Extension\LanguageServer\Protocol;

class RequestMessage extends Message
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $method;

    /**
     * @var array
     */
    public $params;

    public function __construct(string $id, string $method, array $params)
    {
        $this->id = $id;
        $this->method = $method;
        $this->params = $params;
    }
}
