<?php

namespace Phpactor\Application;

use Phpactor\Filesystem\Domain\FilesystemRegistry;
use Phpactor\WorseReflection\Reflector;
use Phpactor\Filesystem\Domain\FilePath;
use Phpactor\Phpactor;
use Phpactor\WorseReflection\Core\Inference\Problems;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\Core\OffsetContext;
use Phpactor\WorseReflection\Core\Inference\SymbolInformation;

class Doctor
{
    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var FilesystemRegistry
     */
    private $filesystem;

    public function __construct(Reflector $reflector, FilesystemRegistry $filesystem)
    {
        $this->reflector = $reflector;
        $this->filesystem = $filesystem;
    }

    public function diagnose(string $path, string $filesystem = 'composer')
    {
        $path = Phpactor::normalizePath($path);
        $results = [];
        $filesystem = $this->filesystem->get($filesystem);

        $files = $filesystem->fileList()->within(FilePath::fromString($path));

        foreach ($files as $file) {
            $source = file_get_contents($file->path());
            $frame = $this->reflector->frame($source);

            $problems = $frame->reduce(function (Frame $frame, Problems $problems) {
                return $problems->merge($frame->problems());
            }, Problems::create());

            if ($problems->none()) {
                continue;
            }

            $results[$file->path()] = [];

            /** @var SymbolInformation $problem */
            foreach ($problems as $problem) {
                $context = OffsetContext::fromSourceAndOffset($source, $problem->symbol()->position()->start(), $problem->symbol()->position()->end());
                $results[$file->path()][] = [
                    'offset' => $context->offset(),
                    'line_number' => $context->lineNumber(),
                    'line' => $context->line(),
                    'column' => $context->col(),
                    'selected' => $context->selected(),
                    'problem' => implode('", "', $problem->issues())
                ];
            }
        }

        return $results;
    }
}
