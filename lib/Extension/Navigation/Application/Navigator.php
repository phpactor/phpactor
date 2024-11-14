<?php

namespace Phpactor\Extension\Navigation\Application;

use Phpactor\Extension\Navigation\Navigator\Navigator as NavigatorInterface;
use Phpactor\Filesystem\Domain\FilePath;
use RuntimeException;
use Phpactor\Extension\CodeTransformExtra\Application\ClassNew;

class Navigator
{
    public function __construct(
        private NavigatorInterface $navigator,
        private ClassNew $classNew,
        private array $autoCreateConfig,
        private FilePath $path
    ) {
    }

    public function destinationsFor(string $path)
    {
        return $this->navigator->destinationsFor($path);
    }

    public function canCreateNew(string $path, string $destinationName)
    {
        $destination = $this->destination($path, $destinationName);

        if (file_exists($destination)) {
            return false;
        }

        return isset($this->autoCreateConfig[$destinationName]);
    }

    public function createNew(string $path, string $destinationName): void
    {
        $destination = $this->destination($path, $destinationName);
        $variant = $this->variant($destinationName);
        $this->classNew->generate($destination, $variant);
    }

    private function destination(string $path, string $destinationName)
    {
        $destinations = $this->destinationsFor($path);

        if (false === isset($destinations[$destinationName])) {
            throw new RuntimeException(sprintf(
                'Destination "%s" does not exist, known destinations: "%s"',
                $destinationName,
                implode('", "', array_keys($destinations))
            ));
        }

        return $this->path->makeAbsoluteFromString($destinations[$destinationName]);
    }

    private function variant(string $destinationName)
    {
        if (!isset($this->autoCreateConfig[$destinationName])) {
            throw new RuntimeException(sprintf(
                'Destination "%s" has no new class variant set',
                $destinationName
            ));
        }

        return $this->autoCreateConfig[$destinationName];
    }
}
