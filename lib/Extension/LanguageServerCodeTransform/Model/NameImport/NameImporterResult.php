<?php

declare(strict_types=1);

namespace Phpactor\Extension\LanguageServerCodeTransform\Model\NameImport;

use Phpactor\CodeTransform\Domain\Refactor\ImportClass\NameImport;
use Phpactor\LanguageServerProtocol\TextEdit as LspTextEdit;
use Throwable;

class NameImporterResult
{
    private function __construct(
        private readonly bool $success,
        private readonly ?NameImport $nameImport,
        private readonly ?array $textEdits,
        private readonly ?Throwable $error
    ) {
    }

    public function hasTextEdits(): bool
    {
        return $this->textEdits !== [];
    }

    /**
     * @return array<LspTextEdit>|null
     */
    public function getTextEdits(): ?array
    {
        return $this->textEdits;
    }

    public function getNameImport(): ?NameImport
    {
        return $this->nameImport;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isSuccessAndHasAliasedNameImport(): bool
    {
        return $this->isSuccess() === true
            && $this->getNameImport() !== null
            && $this->getNameImport()->alias() !== null;
    }

    public function getError(): ?Throwable
    {
        return $this->error;
    }

    public static function createEmptyResult(): NameImporterResult
    {
        return new NameImporterResult(true, null, null, null);
    }

    /**
     * @param array<LspTextEdit>|null $textEdits
     */
    public static function createResult(
        NameImport $nameImport,
        ?array $textEdits
    ): NameImporterResult {
        return new NameImporterResult(true, $nameImport, $textEdits, null);
    }

    public static function createErrorResult(Throwable $error): NameImporterResult
    {
        return new NameImporterResult(false, null, null, $error);
    }
}
