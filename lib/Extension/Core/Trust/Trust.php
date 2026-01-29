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
        return isset($this->trust[$path]);
    }

    public function isTrusted(string $path): bool
    {
        if ($this->unconditionalTrust) {
            return true;
        }

        if (!$this->hasTrust($path)) {
            return false;
        }

        return $this->trust[$path];
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
