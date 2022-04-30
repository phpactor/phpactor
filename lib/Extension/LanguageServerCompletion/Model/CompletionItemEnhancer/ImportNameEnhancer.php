<?php

namespace Phpactor\Extension\LanguageServerCompletion\Model\CompletionItemEnhancer;

use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\Extension\LanguageServerCodeTransform\Model\NameImport\NameImporter;
use Phpactor\Extension\LanguageServerCodeTransform\Model\NameImport\NameImporterResult;
use Phpactor\Extension\LanguageServerCompletion\Model\CompletionItemEnhancer;
use Phpactor\LanguageServerProtocol\CompletionItem;

class ImportNameEnhancer implements CompletionItemEnhancer
{
    private NameImporter $nameImporter;

    public function __construct(NameImporter $nameImporter)
    {
        $this->nameImporter = $nameImporter;
    }

    public function enhance(CompletionItem $item, EnhancerContext $context): CompletionItem
    {
        $result = $this->importClassOrFunctionName($context);

        if (!$result->isSuccess()) {
            return $item;
        }

        $item->additionalTextEdits = $result->getTextEdits();

        $newEdit = $item->textEdit;
        if ($item->additionalTextEdits && $newEdit && $result->isSuccessAndHasAliasedNameImport()) {
            $newEdit->newText = $result->getNameImport()->alias();
            $item->additionalTextEdits[] = $newEdit;
        }

        dump($item);
        return $item;
    }

    private function importClassOrFunctionName(EnhancerContext $context): NameImporterResult {
        $suggestionNameImport = $context->nameImport;

        if (!$suggestionNameImport) {
            return NameImporterResult::createEmptyResult();
        }

        $suggestionType = $context->suggestionType;

        if (!in_array($suggestionType, [ 'class', 'function'])) {
            return NameImporterResult::createEmptyResult();
        }

        $offset = PositionConverter::positionToByteOffset($context->position, $context->textDocument->text);

        return ($this->nameImporter)(
            $context->textDocument,
            $offset->toInt(),
            $suggestionType,
            $suggestionNameImport,
            false
        );
    }
}
