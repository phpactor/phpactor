<?php

namespace Phpactor\Extension\LanguageServer\Server;

use Phpactor\Extension\LanguageServer\Protocol\TextDocument;
use RuntimeException;

class Workspace
{
    /**
     * @var string[]
     */
    private $files = [];

    public function register(string $uri, string $contents)
    {
        $this->files[$uri] = $contents;
    }

    public function deregister(string $uri)
    {
        unset($this->files[$uri]);
    }

    public function get(string $uri): TextDocument
    {
        if (!isset($this->files[$uri])) {
            throw new RuntimeException(sprintf(
                'File "%s" has not been registered',
                $uri
            ));
        }

        return $this->files[$uri];
    }
}
