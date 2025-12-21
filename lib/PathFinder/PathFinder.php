<?php

namespace Phpactor\PathFinder;

use Phpactor\PathFinder\Exception\NoMatchingSourceException;
use Symfony\Component\Filesystem\Path;

class PathFinder
{
    /**
     * @param array<string, Pattern> $destinations
     */
    private function __construct(
        private string $basePath,
        private array $destinations
    ) {
    }

    /**
     * @param array<string, string> $destinations
     */
    public static function fromDestinations(array $destinations): PathFinder
    {
        return new self('', array_map(function (string $pattern) {
            return Pattern::fromPattern($pattern);
        }, $destinations));
    }

    /**
     * @param array<string, string> $destinations
     */
    public static function fromAbsoluteDestinations(string $basePath, array $destinations): PathFinder
    {
        return new self($basePath, array_map(function (string $pattern) {
            return Pattern::fromPattern($pattern);
        }, $destinations));
    }

    /**
     * Return a hash map of destination names to paths representing
     * paths which relate to the given file path.
     *
     * @throws NoMatchingSourceException
     * @return array<string,string>
     */
    public function destinationsFor(string $filePath): array
    {
        if ($this->basePath !== '') {
            $filePath = Path::makeRelative($filePath, $this->basePath);
        }

        $destinations = [];
        $sourcePattern = $this->findSourcePattern($filePath);

        foreach ($this->destinations as $name => $pattern) {
            assert($pattern instanceof Pattern);
            if ($pattern === $sourcePattern) {
                continue;
            }

            $tokens = $sourcePattern->tokens($filePath);
            $destinations[$name] = $pattern->replaceTokens($tokens);
        }

        return $destinations;
    }

    private function findSourcePattern(string $filePath): Pattern
    {
        foreach ($this->destinations as $name => $pattern) {
            assert($pattern instanceof Pattern);
            if ($pattern->fits($filePath)) {
                return $pattern;
            }
        }

        throw new NoMatchingSourceException(sprintf(
            'Could not find matching source pattern for "%s", known patterns: "%s"',
            $filePath,
            implode('", "', array_map(function (Pattern $pattern) {
                return $pattern->toString();
            }, $this->destinations))
        ));
    }
}
