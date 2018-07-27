<?php

namespace Phpactor\Extension\LanguageServer\Protocol;

class ResponseMessage extends Message
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var mixed
     */
    public $result;

    /**
     * @var ResponseError
     */
    public $error;

    public function __construct(string $id = null, $result, ResponseError $error = null)
    {
        $this->id = $id;
        $this->result = $result;
        $this->error = $error;
    }
}
