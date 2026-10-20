<?php

namespace Phpactor\Extension\Core\Trust;

use RuntimeException;

class Trust
{
    private bool $unconditionalTrust;

    /**
     * @param array<string,bool> $trust
     */
    public function __construct(
        public array $trust,
        public readonly ?string $path
    ) {
        $this->unconditionalTrust = (bool)getenv('PHPACTOR_UNCONDITIONAL_TRUST');
    }

    public static function load(string $trustPath): self
    {
        if (!file_exists($trustPath)) {
            return new self([], $trustPath);
        }

        $trustContents = file_get_contents($trustPath);
        if (false === $trustContents) {
            throw new RuntimeException(sprintf(
                'Could not read trust file "%s"',
                $trustPath
            ));
        }

        $trust = json_decode($trustContents, true);

        // file is invalid, return empty so that it will be overwritten
        if (!is_array($trust)) {
            return new self([], $trustPath);
        }

        return new self($trust, $trustPath);
    }

    public function setTrusted(string $path, bool $trust): void
    {
        $this->trust[$path] = $trust;
        $this->writeTrust();
    }

    public function hasTrust(string $path): bool
    {
        return null !== $this->trustFor($path);
    }

    public function isTrusted(string $path): bool
    {
        if ($this->unconditionalTrust) {
            return true;
        }

        return $this->trustFor($path) ?? false;
    }

    /**
     * Exact paths take precedence, otherwise the longest matching pattern wins
     * (so a more specific pattern can exclude a directory from a broader one).
     */
    private function trustFor(string $path): ?bool
    {
        if (isset($this->trust[$path])) {
            return $this->trust[$path];
        }

        $trusted = null;
        $matchedLength = 0;

        foreach ($this->trust as $pattern => $value) {
            $pattern = $this->expandHome($pattern);

            if (strlen($pattern) <= $matchedLength) {
                continue;
            }

            if ($path === $pattern || (str_contains($pattern, '*') && $this->matches($pattern, $path))) {
                $trusted = $value;
                $matchedLength = strlen($pattern);
            }
        }

        return $trusted;
    }

    private function matches(string $pattern, string $path): bool
    {
        $parts = array_map(
            fn (string $part): string => preg_quote($part, '/'),
            explode('*', $pattern)
        );

        return 1 === preg_match('/^' . implode('.*', $parts) . '$/D', $path);
    }

    private function expandHome(string $pattern): string
    {
        if ('~' !== $pattern && !str_starts_with($pattern, '~/') && !str_starts_with($pattern, '~\\')) {
            return $pattern;
        }

        $home = getenv('HOME') ?: getenv('USERPROFILE');

        if (false === $home || '' === $home) {
            return $pattern;
        }

        return $home . substr($pattern, 1);
    }

    private function writeTrust(): void
    {
        if (null === $this->path) {
            throw new RuntimeException('Cannot write Trust as no trust file path was provided');
        }
        if (!file_exists(dirname($this->path))) {
            $success = @mkdir(dirname($this->path), 0755, true);
            if (!$success) {
                throw new RuntimeException(sprintf(
                    'Could not create directory: "%s"',
                    dirname($this->path)
                ));
            }
        }
        $written = file_put_contents($this->path, json_encode($this->trust, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));
        if (false === $written) {
            throw new RuntimeException(sprintf(
                'Could not write trust file to "%s"',
                $this->path
            ));
        }
    }
}
