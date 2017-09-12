<?php

namespace Phpactor\Rpc\Editor;

use Phpactor\Rpc\Action;
use Phpactor\Rpc\Editor\Reference\FileReferences;
use Phpactor\Rpc\Editor\Reference\Reference;

class FileReferencesAction implements Action
{
    /**
     * @var array
     */
    private $references;

    private function __construct(array $references)
    {
        $this->references = $references;
    }

    public static function fromArray(array $array)
    {
        $references = [];
        foreach ($array as $fileAndReferences) {
            $references[] = FileReferences::fromPathAndReferences(
                $fileAndReferences['file'],
                array_map(function (array $reference) {
                    return Reference::fromStartAndEnd($reference['start'], $reference['end']);
                }, $fileAndReferences['references'])
            );
        }

        return new self($references);
    }

    public function name(): string
    {
        return 'echo';
    }

    public function parameters(): array
    {
        return [
            'file_references' => array_map(function (FileReferences $fileReferences) {
                return $fileReferences->toArray();
            }, $this->references)
        ];
    }

    public function references(): string
    {
        return $this->references;
    }
}
