<?php

namespace Phpactor\ClassMover\Domain;

use Phpactor\ClassMover\Domain\Name\FullyQualifiedName;
use InvalidArgumentException;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentLanguage;
use Phpactor\TextDocument\TextDocumentUri;
use RuntimeException;

class SourceCode implements TextDocument
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
                "\n" . sprintf('namespace %s;', (string) $namespace)
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
            return $this->insertAfter($namespaceLineNb, "\n".$useStmt);
        }

        if (null !== $phpDeclarationLineNb) {
            return $this->insertAfter($phpDeclarationLineNb, "\n".$useStmt);
        }

        throw new InvalidArgumentException(
            'Could not find <?php start tag'
        );
    }

    public function replaceSource(string $source): self
    {
        return new self($source);
    }

    public function uri(): ?TextDocumentUri
    {
        return null;
    }

    public function language(): TextDocumentLanguage
    {
        return TextDocumentLanguage::fromString(TextDocumentLanguage::LANGUAGE_PHP);
    }

    public function uriOrThrow(): TextDocumentUri
    {
        throw new RuntimeException('Class mover source code does not currently have a URI');
    }

    private function insertAfter(int $lineNb, string $text): self
    {
        $lines = explode("\n", $this->source);
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

        return $this->replaceSource(implode("\n", $newLines));
    }

    /** @return array{int|null, int|null, int|null} */
    private function significantLineNumbers(): array
    {
        $lines = explode("\n", $this->source);
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
