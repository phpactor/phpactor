<?php

namespace Phpactor\CodeBuilder\Domain\TemplatePathResolver;

use Iterator;
use FilterIterator;
use RuntimeException;
use SplFileInfo;
use function get_debug_type;
use function preg_match;
use function sprintf;
use function version_compare;

class FilterPhpVersionDirectoryIterator extends FilterIterator
{
    /**
     *      @see https://www.php.net/manual/en/reserved.constants.php#reserved.constants.core
     */
    public function __construct(
        Iterator $iterator,
        private string $phpVersion
    ) {
        parent::__construct($iterator);
    }


    public function accept(): bool
    {
        $file = $this->current();
        if (!$file instanceof SplFileInfo) {
            throw new RuntimeException(
                sprintf(
                    'Expected instance of "\SplFileInfo", got "%s".',
                    get_debug_type($file)
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
