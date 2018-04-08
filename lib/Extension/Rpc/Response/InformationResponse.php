<?php

namespace Phpactor\Extension\Rpc\Response;

use Phpactor\Extension\Rpc\Response;

final class InformationResponse implements Response
{
    private $information;

    private function __construct($information)
    {
        $this->information = $information;
    }

    public static function fromString(string $information): InformationResponse
    {
        return new self($information);
    }

    public function information(): string
    {
        return $this->information;
    }

    public function name(): string
    {
        return 'information';
    }

    public function parameters(): array
    {
        return [
            'information' => $this->information,
        ];
    }
}
