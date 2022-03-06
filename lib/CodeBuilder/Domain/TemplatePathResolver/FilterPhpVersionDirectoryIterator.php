<?php

namespace Phpactor\CodeBuilder\Domain\TemplatePathResolver;

use Iterator;
use FilterIterator;
use RuntimeException;
use SplFileInfo;

class FilterPhpVersionDirectoryIterator extends FilterIterator
{
    /**
     * @var string In the form of "major.minor.release[extra]"
     * @see https://www.php.net/manual/en/reserved.constants.php#reserved.constants.core
     */
    private $phpVersion;

    public function __construct(Iterator $iterator, string $phpVersion)
    {
        parent::__construct($iterator);

        $this->phpVersion = $phpVersion;
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

        $filename = $file->getFilename();

        if (!$file->isDir() || // Keep only directy
            !preg_match('/^\d+\.\d+/', $filename) || // Should have at leasts major and minor version
            !version_compare($filename, $this->phpVersion, '<=') // Should be at maximum equals to the defined version
        ) {
            return false;
        }

        return true;
    }
}
