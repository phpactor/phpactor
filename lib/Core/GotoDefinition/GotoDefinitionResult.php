<?php

namespace Phpactor\Core\GotoDefinition;

use Phpactor\Core\GotoDefinition\GotoDefinitionResult;

final class GotoDefinitionResult
{
    /**
     * @var string
     */
    private $path;
    /**
     * @var int
     */
    private $offset;

    private function __construct(string $path, int $offset)
    {
        $this->path = $path;
        $this->offset = $offset;
    }

    public static function fromClassPathAndOffset(string $path, int $offset): GotoDefinitionResult
    {
         return new self($path, $offset);
    }

    public function action(): string
    {
        return 'goto_definition';
    }

    public function arguments(): array
    {
        return [
            'path' => $this->path,
            'offset' => $this->offset,
        ];
    }
}

