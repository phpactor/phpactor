<?php

namespace Phpactor\Extension\Navigation\Application;

use Phpactor\Extension\Navigation\Navigator\Navigator as NavigatorInterface;
use RuntimeException;
use Phpactor\Extension\CodeTransformExtra\Application\ClassNew;
use Symfony\Component\Filesystem\Path;

class Navigator
{
    /** @param array<string, string> $autoCreateConfig */
    public function __construct(
        private NavigatorInterface $navigator,
        private ClassNew $classNew,
        private array $autoCreateConfig,
        private string $absolutePath
    ) {
    }

    public function destinationsFor(string $path): array
    {
        return $this->navigator->destinationsFor($path);
    }

    public function canCreateNew(string $path, string $destinationName): bool
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

    private function destination(string $path, string $destinationName): string
    {
        $destinations = $this->destinationsFor($path);

        if (false === isset($destinations[$destinationName])) {
            throw new RuntimeException(sprintf(
                'Destination "%s" does not exist, known destinations: "%s"',
                $destinationName,
                implode('", "', array_keys($destinations))
            ));
        }

        return Path::makeAbsolute($destinations[$destinationName], $this->absolutePath);
    }

    private function variant(string $destinationName): string
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
