<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Patch;

use Microsoft\PhpParser\Node\Statement\NamespaceDefinition;
use Microsoft\PhpParser\Node\Statement\NamespaceUseDeclaration;
use Microsoft\PhpParser\Node\NamespaceUseClause;
use Microsoft\PhpParser\Node\NamespaceUseGroupClause;
use Microsoft\PhpParser\Node\Statement\ExpressionStatement;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\Expression\BinaryExpression;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\ResolvedName;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\TraitSelectOrAliasClause;
use Microsoft\PhpParser\TokenKind;
use Microsoft\PhpParser\Node\Expression;
use Microsoft\PhpParser\Node\Expression\AnonymousFunctionCreationExpression;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;

/**
 * This is a hack to allow resolving trait use clauses, which are for some reason
 * not supported in tolerant parser.
 *
 * See: https://github.com/Microsoft/tolerant-php-parser/issues/164
 */
class TolerantQualifiedNameResolver
{
    /**
     * Lists of PHP reserved keywords interpreted as QualifiedName by the Tolerant parser
     * but they should not be resolved to any namespace.
     *
     * @todo Remove iterable when Tolerant parser does not considere it as a QualifiedName
     * @see https://github.com/microsoft/tolerant-php-parser/pull/348
     *
     * @var array<string>
     */
    private const UNRESOLVABLE_KEYWORD = ['self', 'static', 'parent', 'iterable'];

    /**
     * @see \Microsoft\PhpParser\Node\QualifiedName::getResolvedName
     */
    public static function getResolvedName($node, $namespaceDefinition = null)
    {
        // Name resolution not applicable to constructs that define symbol names or aliases.
        if (($node->parent instanceof NamespaceDefinition && $node->parent->name->getStartPosition() === $node->getStartPosition()) ||
            $node->parent instanceof NamespaceUseDeclaration ||
            $node->parent instanceof NamespaceUseClause ||
            $node->parent instanceof NamespaceUseGroupClause ||
            //$node->parent->parent instanceof Node\TraitUseClause ||
            $node->parent instanceof TraitSelectOrAliasClause ||
            ($node->parent instanceof TraitSelectOrAliasClause &&
            ($node->parent->asOrInsteadOfKeyword == null || $node->parent->asOrInsteadOfKeyword->kind === TokenKind::AsKeyword))
        ) {
            return null;
        }

        if (array_search($lowerText = strtolower($node->getText()), self::UNRESOLVABLE_KEYWORD) !== false) {
            return $lowerText;
        }

        // FULLY QUALIFIED NAMES
        // - resolve to the name without leading namespace separator.
        if ($node->isFullyQualifiedName()) {
            return ResolvedName::buildName($node->nameParts, $node->getFileContents());
        }

        // RELATIVE NAMES
        // - resolve to the name with namespace replaced by the current namespace.
        // - if current namespace is global, strip leading namespace\ prefix.
        if ($node->isRelativeName()) {
            return $node->getNamespacedName();
        }

        [$namespaceImportTable, $functionImportTable, $constImportTable] = $node->getImportTablesForCurrentScope();

        // QUALIFIED NAMES
        // - first segment of the name is translated according to the current class/namespace import table.
        // - If no import rule applies, the current namespace is prepended to the name.
        if ($node->isQualifiedName()) {
            return self::tryResolveFromImportTable($node, $namespaceImportTable) ?? $node->getNamespacedName();
        }

        // UNQUALIFIED NAMES
        // - translated according to the current import table for the respective symbol type.
        //   (class-like => namespace import table, constant => const import table, function => function import table)
        // - if no import rule applies:
        //   - all symbol types: if current namespace is global, resolve to global namespace.
        //   - class-like symbols: resolve from current namespace.
        //   - function or const: resolved at runtime (from current namespace, with fallback to global namespace).
        if (self::isConstantName($node)) {
            $resolvedName = self::tryResolveFromImportTable($node, $constImportTable, /* case-sensitive */ true);
            $namespaceDefinition = $node->getNamespaceDefinition();
            if ($namespaceDefinition !== null && $namespaceDefinition->name === null) {
                $resolvedName = $resolvedName ?? ResolvedName::buildName($node->nameParts, $node->getFileContents());
            }
            return $resolvedName;
        } elseif ($node->parent instanceof CallExpression) {
            $resolvedName = self::tryResolveFromImportTable($node, $functionImportTable);
            if (($namespaceDefinition = $node->getNamespaceDefinition()) === null || $namespaceDefinition->name === null) {
                $resolvedName = $resolvedName ?? ResolvedName::buildName($node->nameParts, $node->getFileContents());
            }
            return $resolvedName;
        }

        return self::tryResolveFromImportTable($node, $namespaceImportTable) ?? $node->getNamespacedName();
    }

    /**
     * @param ResolvedName[] $importTable
     * @return null
     */
    private static function tryResolveFromImportTable($node, $importTable, bool $isCaseSensitive = false)
    {
        $content = $node->getFileContents();
        $index = $node->nameParts[0]->getText($content);
        //        if (!$isCaseSensitive) {
        //            $index = strtolower($index);
        //        }
        if (isset($importTable[$index])) {
            $resolvedName = $importTable[$index];
            $resolvedName->addNameParts(\array_slice($node->nameParts, 1), $content);
            return $resolvedName;
        }
        return null;
    }

    private static function isConstantName($node) : bool
    {
        return
            ($node->parent instanceof ExpressionStatement || $node->parent instanceof Expression) &&
            !(
                $node->parent instanceof MemberAccessExpression || $node->parent instanceof CallExpression ||
                $node->parent instanceof ObjectCreationExpression ||
                $node->parent instanceof ScopedPropertyAccessExpression || $node->parent instanceof AnonymousFunctionCreationExpression ||
                ($node->parent instanceof BinaryExpression && $node->parent->operator->kind === TokenKind::InstanceOfKeyword)
            );
    }
}
