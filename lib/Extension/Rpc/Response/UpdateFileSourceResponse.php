<?php

namespace Phpactor\Extension\Rpc\Response;

use Phpactor\Extension\Rpc\Diff\TextEditBuilder;
use Phpactor\Extension\Rpc\Response;

class UpdateFileSourceResponse implements Response
{
    private readonly TextEditBuilder $textEditBuilder;

    private function __construct(
        private readonly string $path,
        private readonly string $oldSource,
        private readonly string $newSource
    ) {
        // TODO: This should be a service
        $this->textEditBuilder = new TextEditBuilder();
    }

    public static function fromPathOldAndNewSource(string $path, string $oldSource, string $newSource)
    {
        return new self($path, $oldSource, $newSource);
    }

    public function name(): string
    {
        return 'update_file_source';
    }

    public function parameters(): array
    {
        return [
            'path' => $this->path,
            'source' => $this->newSource,
            'edits' => $this->textEditBuilder->calculateTextEdits($this->oldSource, $this->newSource),
        ];
    }

    public function path(): string
    {
        return $this->path;
    }

    public function oldSource(): string
    {
        return $this->oldSource;
    }

    public function newSource(): string
    {
        return $this->newSource;
    }
}
