<?php

namespace Phpactor\WorseReflection\Core;

use Microsoft\PhpParser\Node\CatchClause;
use Microsoft\PhpParser\Node\ConstElement;
use Microsoft\PhpParser\Node\DelimitedList\QualifiedNameList;
use Microsoft\PhpParser\Node\EnumCaseDeclaration;
use Microsoft\PhpParser\Node\Expression\AnonymousFunctionCreationExpression;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\ArrayCreationExpression;
use Microsoft\PhpParser\Node\Expression\ArrowFunctionCreationExpression;
use Microsoft\PhpParser\Node\Expression\AssignmentExpression;
use Microsoft\PhpParser\Node\Expression\BinaryExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\CastExpression;
use Microsoft\PhpParser\Node\Expression\CloneExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Node\Expression\ParenthesizedExpression;
use Microsoft\PhpParser\Node\Expression\PostfixUpdateExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\Expression\SubscriptExpression;
use Microsoft\PhpParser\Node\Expression\TernaryExpression;
use Microsoft\PhpParser\Node\Expression\UnaryOpExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\Expression\YieldExpression;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\NumericLiteral;
use Microsoft\PhpParser\Node\Parameter;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\ReservedWord;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Microsoft\PhpParser\Node\Statement\EnumDeclaration;
use Microsoft\PhpParser\Node\Statement\ExpressionStatement;
use Microsoft\PhpParser\Node\Statement\ForeachStatement;
use Microsoft\PhpParser\Node\Statement\FunctionDeclaration;
use Microsoft\PhpParser\Node\Statement\GlobalDeclaration;
use Microsoft\PhpParser\Node\Statement\IfStatementNode;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\Node\Statement\ReturnStatement;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Microsoft\PhpParser\Node\StaticVariableDeclaration;
use Microsoft\PhpParser\Node\StringLiteral;
use Microsoft\PhpParser\Node\UseVariableName;
use Phpactor\WorseReflection\Core\Inference\FunctionStubRegistry;
use Phpactor\WorseReflection\Core\Inference\FunctionStub\ArrayMapStub;
use Phpactor\WorseReflection\Core\Inference\FunctionStub\ArrayMergeStub;
use Phpactor\WorseReflection\Core\Inference\FunctionStub\ArrayPopStub;
use Phpactor\WorseReflection\Core\Inference\FunctionStub\ArrayReduceStub;
use Phpactor\WorseReflection\Core\Inference\FunctionStub\ArrayShiftStub;
use Phpactor\WorseReflection\Core\Inference\FunctionStub\ArraySumStub;
use Phpactor\WorseReflection\Core\Inference\FunctionStub\AssertStub;
use Phpactor\WorseReflection\Core\Inference\FunctionStub\InArrayStub;
use Phpactor\WorseReflection\Core\Inference\FunctionStub\IsSomethingStub;
use Phpactor\WorseReflection\Core\Inference\FunctionStub\IteratorToArrayStub;
use Phpactor\WorseReflection\Core\Inference\FunctionStub\ResetStub;
use Phpactor\WorseReflection\Core\Inference\GenericMapResolver;
use Phpactor\WorseReflection\Core\Inference\NodeToTypeConverter;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\AnonymousFunctionCreationExpressionResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\ArgumentExpressionResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\ArrayCreationExpressionResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\ArrowFunctionCreationExpressionResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\AssignmentExpressionResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\CallExpressionResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\CastExpressionResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\CatchClauseResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\ClassLikeResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\CloneExpressionResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\CompoundStatementResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\ConstElementResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\EnumCaseDeclarationResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\ExpressionStatementResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\ForeachStatementResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\GlobalDeclarationResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\IfStatementResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\MemberAccessExpressionResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\MemberAccess\NodeContextFromMemberAccess;
use Phpactor\WorseReflection\Core\Inference\Resolver\MethodDeclarationResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\NumericLiteralResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\ObjectCreationExpressionResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\ParameterResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\ParenthesizedExpressionResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\PostfixUpdateExpressionResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\ReservedWordResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\ReturnStatementResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\ScopedPropertyAccessResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\SourceFileNodeResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\StaticDeclarationResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\StringLiteralResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\SubscriptExpressionResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\TernaryExpressionResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\UnaryOpExpressionResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\UseVariableNameResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\QualifiedNameResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\QualifiedNameListResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\VariableResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\BinaryExpressionResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\FunctionDeclarationResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\YieldExpressionResolver;
use Phpactor\WorseReflection\Reflector;

final class DefaultResolverFactory
{
    private readonly FunctionStubRegistry $functionStubRegistry;

    public function __construct(
        private readonly Reflector $reflector,
        private readonly NodeToTypeConverter $nodeTypeConverter,
        private readonly GenericMapResolver $genericResolver,
        private readonly NodeContextFromMemberAccess $nodeContextFromMemberAccess
    ) {
        $this->functionStubRegistry = $this->createStubRegistry();
    }

    /**
     * @return array<class-string,Resolver>
     */
    public function createResolvers(): array
    {
        return [
            QualifiedName::class => new QualifiedNameResolver($this->reflector, $this->functionStubRegistry, $this->nodeTypeConverter),
            QualifiedNameList::class => new QualifiedNameListResolver(),
            ConstElement::class => new ConstElementResolver(),
            EnumCaseDeclaration::class => new EnumCaseDeclarationResolver(),
            Parameter::class => new ParameterResolver(),
            UseVariableName::class => new UseVariableNameResolver(),
            GlobalDeclaration::class => new GlobalDeclarationResolver(),
            StaticVariableDeclaration::class => new StaticDeclarationResolver(),
            Variable::class => new VariableResolver(),
            MemberAccessExpression::class => new MemberAccessExpressionResolver($this->nodeContextFromMemberAccess),
            ScopedPropertyAccessExpression::class => new ScopedPropertyAccessResolver($this->nodeContextFromMemberAccess),
            CallExpression::class => new CallExpressionResolver($this->genericResolver),
            ParenthesizedExpression::class => new ParenthesizedExpressionResolver(),
            BinaryExpression::class => new BinaryExpressionResolver(),
            UnaryOpExpression::class => new UnaryOpExpressionResolver(),
            ClassDeclaration::class => new ClassLikeResolver(),
            InterfaceDeclaration::class => new ClassLikeResolver(),
            TraitDeclaration::class => new ClassLikeResolver(),
            EnumDeclaration::class => new ClassLikeResolver(),
            FunctionDeclaration::class => new FunctionDeclarationResolver(),
            ObjectCreationExpression::class => new ObjectCreationExpressionResolver($this->genericResolver),
            SubscriptExpression::class => new SubscriptExpressionResolver(),
            StringLiteral::class => new StringLiteralResolver(),
            NumericLiteral::class => new NumericLiteralResolver(),
            ReservedWord::class => new ReservedWordResolver(),
            ArrayCreationExpression::class => new ArrayCreationExpressionResolver(),
            ArgumentExpression::class => new ArgumentExpressionResolver(),
            TernaryExpression::class => new TernaryExpressionResolver(),
            MethodDeclaration::class => new MethodDeclarationResolver(),
            CloneExpression::class => new CloneExpressionResolver(),
            AssignmentExpression::class => new AssignmentExpressionResolver(),
            CastExpression::class => new CastExpressionResolver(),
            ArrowFunctionCreationExpression::class => new ArrowFunctionCreationExpressionResolver(),
            AnonymousFunctionCreationExpression::class => new AnonymousFunctionCreationExpressionResolver(),
            CatchClause::class => new CatchClauseResolver(),
            ForeachStatement::class => new ForeachStatementResolver(),
            IfStatementNode::class => new IfStatementResolver(),
            CompoundStatementNode::class => new CompoundStatementResolver(),
            ExpressionStatement::class => new ExpressionStatementResolver(),
            SourceFileNode::class => new SourceFileNodeResolver(),
            ReturnStatement::class => new ReturnStatementResolver(),
            YieldExpression::class => new YieldExpressionResolver(),
            PostfixUpdateExpression::class => new PostfixUpdateExpressionResolver(),
        ];
    }

    private function createStubRegistry(): FunctionStubRegistry
    {
        return new FunctionStubRegistry([
            'array_sum' => new ArraySumStub(),
            'in_array' => new InArrayStub(),
            'iterator_to_array' => new IteratorToArrayStub(),
            'is_null' => new IsSomethingStub(TypeFactory::null()),
            'is_float' => new IsSomethingStub(TypeFactory::float()),
            'is_int' => new IsSomethingStub(TypeFactory::int()),
            'is_string' => new IsSomethingStub(TypeFactory::string()),
            'is_callable' => new IsSomethingStub(TypeFactory::callable()),
            'array_map' => new ArrayMapStub(),
            'reset' => new ResetStub(),
            'array_shift' => new ArrayShiftStub(),
            'array_pop' => new ArrayPopStub(),
            'array_reduce' => new ArrayReduceStub(),
            'array_merge' => new ArrayMergeStub(),
            'assert' => new AssertStub(),
        ]);
    }
}
