<?php

namespace Phpactor\Core\GotoDefinition;

use Phpactor\Core\GotoDefinition\GotoDefinitionResult;

final class GotoDefinitionResult implements \JsonSerializable
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

    public function path(): string
    {
        return $this->path;
    }

    public function offset(): int
    {
        return $this->offset;
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize()
    {
        return [
            'path' => $this->path,
            'offset' => $this->offset,
        ];
    }
}

