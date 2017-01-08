<?php

declare(strict_types=1);

namespace Phpactor;

class CodeContext
{
    private $source;
    private $offset;
    private $path;

    private function __construct()
    {
    }

    public static function create(string $path = null, string $source, int $offset)
    {
        $context = new self();
        $context->path = $path;
        $context->source = $source;
        $context->offset = $offset;

        return $context;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
