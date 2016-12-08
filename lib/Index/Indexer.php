<?php

namespace Phpactor\Index;

use Symfony\Component\Finder\Finder;
use Phpactor\Util\ClassUtil;
use \Exception;
use \Closure;
use Phpactor\Index\Index;

class Indexer
{
    /**
     * @var Exception[]
     */
    private $exceptions = [];

    public function __invoke(Index $index, Finder $finder, Closure $progressCallback)
    {
        foreach ($finder as $file) {
            try {
            $classNames = ClassUtil::getClassNamesFromFile($file);

            foreach ($classNames as $className) {
                $index->add($className, $file->getPathname());
            }
            } catch (Exception $e) {
                $this->exceptions[] = $e;
                // continue
            }
            $progressCallback($file);
        }

        return $index;
    }

    public function getExceptions()
    {
        return $this->exceptions;
    }
}
