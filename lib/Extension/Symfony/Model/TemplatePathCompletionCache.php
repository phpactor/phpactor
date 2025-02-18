<?php

namespace Phpactor\Extension\Symfony\Model;

use Generator;
use Phpactor\Completion\Core\Suggestion;
use RecursiveDirectoryIterator;
use SplFileInfo;
use RecursiveIteratorIterator;

final class TemplatePathCompletionCache
{
    public function __construct()
    {
    }

    public function complete(): Generator
    {
        $files = $this->_getFilesRecursively('templates', 'twig');

        foreach ($files as $file) {
            yield Suggestion::createWithOptions(
                $file,
                [
                    'label' => $file,
                    'short_description' => '',
                    'documentation' => '',
                    'type' => Suggestion::TYPE_FILE,
                    'priority' => 555,
                ]
            );
        }

        return true;
    }

    /**
     * @return Generator<string>
     */
    private function _getFilesRecursively(string $path, string $filetype): Generator
    {
        if (!is_dir($path)) {
            return true;
        }

        $directoryInfo = new SplFileInfo($path);
        $directoryIterator = new RecursiveDirectoryIterator($path);
        $iterator = new RecursiveIteratorIterator($directoryIterator);

        /**
        * @var SplFileInfo $file filename
        */
        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getExtension(), $filetype)) {
                $path = $file->getPathname();
                if (str_starts_with($path, $directoryInfo->getPathname())) {
                    $path = substr($path, strlen($directoryInfo->getPathname()));
                }

                yield ltrim($path, '/');
            }
        }

        return '';
    }
}
