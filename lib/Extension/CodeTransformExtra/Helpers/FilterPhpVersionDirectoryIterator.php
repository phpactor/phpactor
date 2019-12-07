<?php

namespace Phpactor\Extension\CodeTransformExtra\Helpers;

use Iterator;
use FilterIterator;
use RuntimeException;
use SplFileInfo;

class FilterPhpVersionDirectoryIterator extends FilterIterator
{
    /**
     * @var int
     */
    private $phpVersionId;

    public function __construct(Iterator $iterator, int $phpVersionId)
    {
        parent::__construct($iterator);

        $this->phpVersionId = $phpVersionId;
    }

    /**
     * {@inheritDoc}
     */
    public function accept()
    {
        $file = $this->current();
        if (!$file instanceof SplFileInfo) {
            throw new RuntimeException(
                sprintf(
                    'Expected instance of "\SplFileInfo", got "%s".',
                    \is_object($file) ? \get_class($file) : \gettype($file)
                )
            );
        }

        $path = $file->isLink() ? $file->getPathname() : $file->getRealPath();
        $filename = $file->getFilename();

        if (!$file->isDir() || // Keep only directy
            !preg_match('/^\d{5}$/', $filename) || // Should be formed of 5 digits
            $this->phpVersionId < (int) $filename // Ignore next versions of PHP
        ) {
            return false;
        }

        return true;
    }
}
