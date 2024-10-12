<?php

namespace Phpactor\Tests\PHPStan\Rule;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;

/**
 * @implements Rule<FuncCall>
 */
class NoDumpRule implements Rule
{
    private const DISALLOWED_CALLS = ['dump', 'dd', 'var_dump', 'exit', 'die'];

    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        assert($node instanceof FuncCall);

        if (!$node->name instanceof Name) {
            return [];
        }
        if (!in_array($node->name->__toString(), self::DISALLOWED_CALLS)) {
            return [];
        }
        return [
            RuleErrorBuilder::message(
                sprintf('Function call "%s" is not allowed', $node->name->__toString())
            )->build(),
        ];
    }
}
