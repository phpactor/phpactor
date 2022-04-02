<?php

namespace Phpactor\WorseReflection\Core;

use Microsoft\PhpParser\Node\ConstElement;
use Microsoft\PhpParser\Node\DelimitedList\QualifiedNameList;
use Microsoft\PhpParser\Node\EnumCaseDeclaration;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\ArrayCreationExpression;
use Microsoft\PhpParser\Node\Expression\BinaryExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\CloneExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Node\Expression\ParenthesizedExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\Expression\SubscriptExpression;
use Microsoft\PhpParser\Node\Expression\TernaryExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\NumericLiteral;
use Microsoft\PhpParser\Node\Parameter;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\ReservedWord;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\EnumDeclaration;
use Microsoft\PhpParser\Node\Statement\FunctionDeclaration;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Microsoft\PhpParser\Node\StringLiteral;
use Microsoft\PhpParser\Node\UseVariableName;
use Phpactor\WorseReflection\Core\Inference\FullyQualifiedNameResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\ArgumentExpressionResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\ArrayCreationExpressionResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\CallExpressionResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\ClassLikeResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\CloneExpressionResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\ConstElementResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\EnumCaseDeclarationResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\MemberAccessExpressionResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\MethodDeclarationResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\NumericLiteralResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\ObjectCreationExpressionResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\ParameterResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\ParenthesizedExpressionResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\ReservedWordResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\ScopedPropertyAccessResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\StringLiteralResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\SubscriptExpressionResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\TernaryExpressionResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\UseVariableNameResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\QualifiedNameResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\QualifiedNameListResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\VariableResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\BinaryExpressionResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\FunctionDeclarationResolver;
use Phpactor\WorseReflection\Reflector;

final class DefaultResolverFactory
{
    private Reflector $reflector;

    private FullyQualifiedNameResolver $nodeTypeConverter;

    public function __construct(
        Reflector $reflector,
        FullyQualifiedNameResolver $nodeTypeConverter
    ) {
        $this->reflector = $reflector;
        $this->nodeTypeConverter = $nodeTypeConverter;
    }

    /**
     * @return array<class-string,Resolver>
     */
    public function createResolvers(): array
    {
        return [
            QualifiedName::class => new QualifiedNameResolver($this->reflector, $this->nodeTypeConverter),
            QualifiedNameList::class => new QualifiedNameListResolver(),
            ConstElement::class => new ConstElementResolver(),
            EnumCaseDeclaration::class => new EnumCaseDeclarationResolver(),
            Parameter::class => new ParameterResolver(),
            UseVariableName::class => new UseVariableNameResolver(),
            Variable::class => new VariableResolver(),
            MemberAccessExpression::class => new MemberAccessExpressionResolver(),
            ScopedPropertyAccessExpression::class => new ScopedPropertyAccessResolver($this->nodeTypeConverter),
            CallExpression::class => new CallExpressionResolver(),
            ParenthesizedExpression::class => new ParenthesizedExpressionResolver(),
            BinaryExpression::class => new BinaryExpressionResolver(),
            ClassDeclaration::class => new ClassLikeResolver(),
            InterfaceDeclaration::class => new ClassLikeResolver(),
            TraitDeclaration::class => new ClassLikeResolver(),
            EnumDeclaration::class => new ClassLikeResolver(),
            FunctionDeclaration::class => new FunctionDeclarationResolver(),
            ObjectCreationExpression::class => new ObjectCreationExpressionResolver(),
            SubscriptExpression::class => new SubscriptExpressionResolver(),
            StringLiteral::class => new StringLiteralResolver(),
            NumericLiteral::class => new NumericLiteralResolver(),
            ReservedWord::class => new ReservedWordResolver(),
            ArrayCreationExpression::class => new ArrayCreationExpressionResolver(),
            ArgumentExpression::class => new ArgumentExpressionResolver(),
            TernaryExpression::class => new TernaryExpressionResolver(),
            MethodDeclaration::class => new MethodDeclarationResolver(),
            CloneExpression::class => new CloneExpressionResolver(),
        ];
    }
}
