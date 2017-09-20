<?php

namespace Phpactor\Rpc\Editor;

use Phpactor\Rpc\Action;

final class InformationAction implements Action
{
    private $information;

    private function __construct($information)
    {
        $this->information = $information;
    }

    public static function fromString(string $information): InformationAction
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
