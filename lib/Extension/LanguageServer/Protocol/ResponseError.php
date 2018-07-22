<?php

namespace Phpactor\Extension\LanguageServer\Protocol;

class ResponseError
{
    // Defined by JSON RPC
    public const ParseError = -32700;
    public const InvalidRequest = -32600;
    public const MethodNotFound = -32601;
    public const InvalidParams = -32602;
    public const InternalError = -32603;
    public const serverErrorStart = -32099;
    public const serverErrorEnd = -32000;
    public const ServerNotInitialized = -32002;
    public const UnknownErrorCode = -32001;

    // Defined by the protocol.
    public const RequestCancelled = -32800;

    /**
     * @var int
     */
    public $code;

    /**
     * @var string
     */
    public $message;

    /**
     * @var mixed
     */
    public $data;

    public function __construct(int $code, string $message, $data = null)
    {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }
}
