<?php

namespace Phpactor\ClassMover\Domain;

use Phpactor\ClassMover\Domain\Name\FullyQualifiedName;
use InvalidArgumentException;

class SourceCode
{
    public function __construct(private string $source)
    {
    }

    public function __toString(): string
    {
        return $this->source;
    }

    public static function fromString(string $source): SourceCode
    {
        return new self($source);
    }

    public function addNamespace(FullyQualifiedName $namespace): SourceCode
    {
        [$phpDeclarationLineNb, $namespaceLineNb] = $this->significantLineNumbers();

        if (null !== $namespaceLineNb) {
            return $this;
        }

        if (null !== $phpDeclarationLineNb) {
            return $this->insertAfter(
                $phpDeclarationLineNb,
                PHP_EOL . sprintf('namespace %s;', (string) $namespace)
            );
        }

        return new self($this->source);
    }

    public function addUseStatement(FullyQualifiedName $classToUse): SourceCode
    {
        $useStmt = 'use '.$classToUse->__toString().';';

        $namespaceLineNb = null;
        $lastUseLineNb = null;
        $phpDeclarationLineNb = null;

        [$phpDeclarationLineNb, $namespaceLineNb, $lastUseLineNb] = $this->significantLineNumbers();

        if ($lastUseLineNb) {
            return $this->insertAfter($lastUseLineNb, $useStmt);
        }

        if ($namespaceLineNb) {
            return $this->insertAfter($namespaceLineNb, PHP_EOL.$useStmt);
        }

        if (null !== $phpDeclarationLineNb) {
            return $this->insertAfter($phpDeclarationLineNb, PHP_EOL.$useStmt);
        }

        throw new InvalidArgumentException(
            'Could not find <?php start tag'
        );
    }

    public function replaceSource(string $source): self
    {
        return new self($source);
    }

    private function insertAfter(int $lineNb, string $text): self
    {
        $lines = explode(PHP_EOL, $this->source);
        $newLines = [];
        foreach ($lines as $index => $line) {
            if ($line === $text) {
                return $this;
            }

            $newLines[] = $line;
            if ($index === $lineNb) {
                $newLines[] = $text;
            }
        }

        return $this->replaceSource(implode(PHP_EOL, $newLines));
    }

    /** @return array{int|null, int|null, int|null} */
    private function significantLineNumbers(): array
    {
        $lines = explode(PHP_EOL, $this->source);
        $phpDeclarationLineNb = $namespaceLineNb = $lastUseLineNb = null;

        foreach ($lines as $index => $line) {
            if (preg_match('{^<\?php}', $line)) {
                $phpDeclarationLineNb = $index;
            }

            if (preg_match('{^namespace}', $line)) {
                $namespaceLineNb = $index;
            }

            if (preg_match('{^use}', $line)) {
                $lastUseLineNb = $index;
            }
        }

        return [ $phpDeclarationLineNb, $namespaceLineNb, $lastUseLineNb ];
    }
}
