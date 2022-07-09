<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Transformer;

use Microsoft\PhpParser\Node\DelimitedList\NamespaceUseClauseList;
use Microsoft\PhpParser\Node\NamespaceUseClause;
use Microsoft\PhpParser\Node\NamespaceUseGroupClause;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\Statement\NamespaceUseDeclaration;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\TokenKind;
use Phpactor\CodeTransform\Domain\Diagnostic;
use Phpactor\CodeTransform\Domain\Diagnostics;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Domain\Transformer;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\UnusedImportDiagnostic;
use Phpactor\WorseReflection\Reflector;

class RemoveUnusedImportsTransformer implements Transformer
{
    private Reflector $reflector;

    private Parser $parser;


    public function __construct(Reflector $reflector, Parser $parser)
    {
        $this->reflector = $reflector;
        $this->parser = $parser;
    }

    public function transform(SourceCode $code): TextEdits
    {
        $rootNode = $this->parser->parseSourceFile($code);
        $edits = [];

        foreach ($this->unusedImports($code) as $unusedImport) {
            $importNode = $rootNode->getDescendantNodeAtPosition($unusedImport->range()->start()->toInt());

            if (!$importNode instanceof QualifiedName) {
                continue;
            }

            $list = $importNode->getFirstAncestor(NamespaceUseClause::class);

            if (!$list instanceof NamespaceUseClause) {
                continue;
            }

            if ($list->groupClauses) {
                if ($edit = $this->forGroupClause($importNode, $list)) {
                    $edits[] = $edit;
                }
                continue;
            }

            // there is exactly one element
            $declaration = $importNode->getFirstAncestor(NamespaceUseDeclaration::class);
            $length = $declaration->getEndPosition() - $declaration->getStartPosition();

            if (substr($code->__toString(), $declaration->getEndPosition(), 1) === "\n") {
                $length++;
            }

            $edits[] = TextEdit::create(
                $declaration->getStartPosition(),
                $length,
                ''
            );


        }

        return TextEdits::fromTextEdits($edits);
    }

    public function diagnostics(SourceCode $code): Diagnostics
    {
        $diagnostics = [];
        foreach ($this->unusedImports($code) as $unusedClass) {
            $diagnostics[] = new Diagnostic(
                $unusedClass->range(),
                $unusedClass->message(),
                Diagnostic::WARNING
            );
        }

        return new Diagnostics($diagnostics);
    }

    private function unusedImports(SourceCode $code): \Phpactor\WorseReflection\Core\Diagnostics
    {
        return $this->reflector->diagnostics($code->__toString())->byClass(UnusedImportDiagnostic::class);
    }

    private function forGroupClause(QualifiedName $importNode, NamespaceUseClause $list): ?TextEdit
    {
        foreach ($list->groupClauses->children as $groupClause) {
            if (!$groupClause instanceof NamespaceUseGroupClause) {
                continue;
            }

            if ($groupClause->namespaceName->__toString() === $importNode->__toString()) {
                die('asd');
            }
        }

        return null;
    }
}
