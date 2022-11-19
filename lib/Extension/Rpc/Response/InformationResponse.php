<?php

namespace Phpactor\Extension\Rpc\Response;

use Phpactor\Extension\Rpc\Response;

final class InformationResponse implements Response
{
    private function __construct(private $information)
    {
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
