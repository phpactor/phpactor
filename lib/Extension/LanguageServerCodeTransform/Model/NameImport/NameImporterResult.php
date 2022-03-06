<?php

declare(strict_types=1);

namespace Phpactor\Extension\LanguageServerCodeTransform\Model\NameImport;

use Phpactor\CodeTransform\Domain\Refactor\ImportClass\NameImport;
use Phpactor\LanguageServerProtocol\TextEdit as LspTextEdit;
use Throwable;

class NameImporterResult
{
    /**
     * @var bool
     */
    private $success;

    /**
     * @var Throwable|null
     */
    private $error;

    /**
     * @var array<LspTextEdit>|null
     */
    private $textEdits;

    /**
     * @var NameImport
     */
    private $nameImport;

    private function __construct(
        bool $success,
        ?NameImport $nameImport,
        ?array $textEdits,
        ?Throwable $error
    ) {
        $this->success = $success;
        $this->nameImport = $nameImport;
        $this->textEdits = $textEdits;
        $this->error = $error;
    }

    public function hasTextEdits(): bool
    {
        return !empty($this->textEdits);
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
     * @param NameImport $nameImport
     * @param array<LspTextEdit>|null $textEdits
     * @return NameImporterResult
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
