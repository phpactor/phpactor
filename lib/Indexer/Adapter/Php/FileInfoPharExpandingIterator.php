<?php

namespace Phpactor\Indexer\Adapter\Php;

use Iterator;
use IteratorAggregate;
use Phar;
use RecursiveIteratorIterator;
use SplFileInfo;
use Traversable;
use UnexpectedValueException;

/**
 * @implements IteratorAggregate<SplFileInfo>
 */
class FileInfoPharExpandingIterator implements IteratorAggregate
{
    /**
     * @param Iterator<SplFileInfo> $innerIterator
     * @param list<string> $supportedExtensions
     */
    public function __construct(private Iterator $innerIterator, private array $supportedExtensions = ['php'])
    {
    }

    public function getIterator(): Traversable
    {
        foreach ($this->innerIterator as $fileInfo) {
            if ($fileInfo->getExtension() !== 'phar') {
                yield $fileInfo;
            }
            try {
                $phar = new Phar($fileInfo->getPathname());
            } catch (UnexpectedValueException) {
                continue;
            }
            $iterator = new RecursiveIteratorIterator($phar);
            foreach ($iterator as $fileInfo) {
                assert($fileInfo instanceof SplFileInfo);
                if (!in_array($fileInfo->getExtension(), $this->supportedExtensions)) {
                    continue;
                }
                yield $fileInfo;
            }
        }
    }
}
