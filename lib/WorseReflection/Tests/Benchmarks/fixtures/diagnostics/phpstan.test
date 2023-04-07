<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use ArrayAccess;
use Closure;
use Generator;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\Cast\Double;
use PhpParser\Node\Expr\Cast\Int_;
use PhpParser\Node\Expr\Cast\Object_;
use PhpParser\Node\Expr\Cast\Unset_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\EncapsedStringPart;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeFinder;
use PHPStan\Node\ExecutionEndNode;
use PHPStan\Node\Expr\GetIterableKeyTypeExpr;
use PHPStan\Node\Expr\GetIterableValueTypeExpr;
use PHPStan\Node\Expr\GetOffsetValueTypeExpr;
use PHPStan\Node\Expr\OriginalPropertyTypeExpr;
use PHPStan\Node\Expr\SetOffsetValueTypeExpr;
use PHPStan\Node\Expr\TypeExpr;
use PHPStan\Node\Printer\ExprPrinter;
use PHPStan\Parser\ArrayMapArgVisitor;
use PHPStan\Parser\NewAssignedToPropertyVisitor;
use PHPStan\Parser\Parser;
use PHPStan\Php\PhpVersion;
use PHPStan\Reflection\Assertions;
use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ConstantReflection;
use PHPStan\Reflection\Dummy\DummyConstructorReflection;
use PHPStan\Reflection\ExtendedMethodReflection;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\InitializerExprContext;
use PHPStan\Reflection\InitializerExprTypeResolver;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\Native\NativeParameterReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ParametersAcceptorWithPhpDocs;
use PHPStan\Reflection\PassedByReference;
use PHPStan\Reflection\Php\DummyParameter;
use PHPStan\Reflection\Php\PhpFunctionFromParserNodeReflection;
use PHPStan\Reflection\Php\PhpMethodFromParserNodeReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Reflection\TrivialParametersAcceptor;
use PHPStan\Rules\Properties\PropertyReflectionFinder;
use PHPStan\ShouldNotHappenException;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Accessory\AccessoryArrayListType;
use PHPStan\Type\Accessory\AccessoryLiteralStringType;
use PHPStan\Type\Accessory\HasOffsetValueType;
use PHPStan\Type\Accessory\NonEmptyArrayType;
use PHPStan\Type\Accessory\OversizedArrayType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\ClosureType;
use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantFloatType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ConstantTypeHelper;
use PHPStan\Type\DynamicReturnTypeExtensionRegistry;
use PHPStan\Type\ErrorType;
use PHPStan\Type\GeneralizePrecision;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Generic\TemplateType;
use PHPStan\Type\Generic\TemplateTypeHelper;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\IntegerRangeType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\NonexistentParentClassType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ParserNodeTypeToPHPStanType;
use PHPStan\Type\StaticType;
use PHPStan\Type\StringType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypehintHelper;
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;
use PHPStan\Type\VoidType;
use Throwable;
use function abs;
use function array_key_exists;
use function array_key_first;
use function array_keys;
use function array_map;
use function array_merge;
use function array_pop;
use function array_slice;
use function count;
use function explode;
use function get_class;
use function implode;
use function in_array;
use function is_string;
use function ltrim;
use function sprintf;
use function str_starts_with;
use function strlen;
use function strtolower;
use function substr;
use function usort;
use const PHP_INT_MAX;
use const PHP_INT_MIN;

class MutatingScope implements Scope
{

	/** @var Type[] */
	private array $resolvedTypes = [];

	/** @var array<string, self> */
	private array $truthyScopes = [];

	/** @var array<string, self> */
	private array $falseyScopes = [];

	private ?string $namespace;

	private ?self $scopeOutOfFirstLevelStatement = null;

	private ?self $scopeWithPromotedNativeTypes = null;

	/**
	 * @param array<string, ExpressionTypeHolder> $expressionTypes
	 * @param array<string, ConditionalExpressionHolder[]> $conditionalExpressions
	 * @param list<string> $inClosureBindScopeClasses
	 * @param array<string, true> $currentlyAssignedExpressions
	 * @param array<string, true> $currentlyAllowedUndefinedExpressions
	 * @param array<string, ExpressionTypeHolder> $nativeExpressionTypes
	 * @param array<MethodReflection|FunctionReflection> $inFunctionCallsStack
	 */
	public function __construct(
		private InternalScopeFactory $scopeFactory,
		private ReflectionProvider $reflectionProvider,
		private InitializerExprTypeResolver $initializerExprTypeResolver,
		private DynamicReturnTypeExtensionRegistry $dynamicReturnTypeExtensionRegistry,
		private ExprPrinter $exprPrinter,
		private TypeSpecifier $typeSpecifier,
		private PropertyReflectionFinder $propertyReflectionFinder,
		private Parser $parser,
		private NodeScopeResolver $nodeScopeResolver,
		private ConstantResolver $constantResolver,
		private ScopeContext $context,
		private PhpVersion $phpVersion,
		private bool $declareStrictTypes = false,
		private FunctionReflection|ExtendedMethodReflection|null $function = null,
		?string $namespace = null,
		private array $expressionTypes = [],
		private array $nativeExpressionTypes = [],
		private array $conditionalExpressions = [],
		private array $inClosureBindScopeClasses = [],
		private ?ParametersAcceptor $anonymousFunctionReflection = null,
		private bool $inFirstLevelStatement = true,
		private array $currentlyAssignedExpressions = [],
		private array $currentlyAllowedUndefinedExpressions = [],
		private array $inFunctionCallsStack = [],
		private bool $afterExtractCall = false,
		private ?Scope $parentScope = null,
		private bool $nativeTypesPromoted = false,
		private bool $explicitMixedInUnknownGenericNew = false,
		private bool $explicitMixedForGlobalVariables = false,
	)
	{
		if ($namespace === '') {
			$namespace = null;
		}

		$this->namespace = $namespace;
	}

	/** @api */
	public function getFile(): string
	{
		return $this->context->getFile();
	}

	/** @api */
	public function getFileDescription(): string
	{
		if ($this->context->getTraitReflection() === null) {
			return $this->getFile();
		}

		/** @var ClassReflection $classReflection */
		$classReflection = $this->context->getClassReflection();

		$className = $classReflection->getDisplayName();
		if (!$classReflection->isAnonymous()) {
			$className = sprintf('class %s', $className);
		}

		$traitReflection = $this->context->getTraitReflection();
		if ($traitReflection->getFileName() === null) {
			throw new ShouldNotHappenException();
		}

		return sprintf(
			'%s (in context of %s)',
			$traitReflection->getFileName(),
			$className,
		);
	}

	/** @api */
	public function isDeclareStrictTypes(): bool
	{
		return $this->declareStrictTypes;
	}

	public function enterDeclareStrictTypes(): self
	{
		return $this->scopeFactory->create(
			$this->context,
			true,
			null,
			null,
			$this->expressionTypes,
			$this->nativeExpressionTypes,
		);

	}

	/** @api */
	public function isInClass(): bool
	{
		return $this->context->getClassReflection() !== null;
	}

	/** @api */
	public function isInTrait(): bool
	{
		return $this->context->getTraitReflection() !== null;
	}

	/** @api */
	public function getClassReflection(): ?ClassReflection
	{
		return $this->context->getClassReflection();
	}

	/** @api */
	public function getTraitReflection(): ?ClassReflection
	{
		return $this->context->getTraitReflection();
	}

	/**
	 * @api
	 * @return FunctionReflection|ExtendedMethodReflection|null
	 */
	public function getFunction()
	{
		return $this->function;
	}

	/** @api */
	public function getFunctionName(): ?string
	{
		return $this->function !== null ? $this->function->getName() : null;
	}

	/** @api */
	public function getNamespace(): ?string
	{
		return $this->namespace;
	}

	/** @api */
	public function getParentScope(): ?Scope
	{
		return $this->parentScope;
	}

	/** @api */
	public function canAnyVariableExist(): bool
	{
		return ($this->function === null && !$this->isInAnonymousFunction()) || $this->afterExtractCall;
	}

	public function afterExtractCall(): self
	{
		return $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$this->expressionTypes,
			$this->nativeExpressionTypes,
			[],
			$this->inClosureBindScopeClasses,
			$this->anonymousFunctionReflection,
			$this->isInFirstLevelStatement(),
			$this->currentlyAssignedExpressions,
			$this->currentlyAllowedUndefinedExpressions,
			$this->inFunctionCallsStack,
			true,
			$this->parentScope,
			$this->nativeTypesPromoted,
		);
	}

	public function afterClearstatcacheCall(): self
	{
		$expressionTypes = $this->expressionTypes;
		foreach (array_keys($expressionTypes) as $exprString) {
			// list from https://www.php.net/manual/en/function.clearstatcache.php

			// stat(), lstat(), file_exists(), is_writable(), is_readable(), is_executable(), is_file(), is_dir(), is_link(), filectime(), fileatime(), filemtime(), fileinode(), filegroup(), fileowner(), filesize(), filetype(), and fileperms().
			foreach ([
				'stat',
				'lstat',
				'file_exists',
				'is_writable',
				'is_writeable',
				'is_readable',
				'is_executable',
				'is_file',
				'is_dir',
				'is_link',
				'filectime',
				'fileatime',
				'filemtime',
				'fileinode',
				'filegroup',
				'fileowner',
				'filesize',
				'filetype',
				'fileperms',
			] as $functionName) {
				if (!str_starts_with($exprString, $functionName . '(') && !str_starts_with($exprString, '\\' . $functionName . '(')) {
					continue;
				}

				unset($expressionTypes[$exprString]);
				continue 2;
			}
		}
		return $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$expressionTypes,
			$this->nativeExpressionTypes,
			$this->conditionalExpressions,
			$this->inClosureBindScopeClasses,
			$this->anonymousFunctionReflection,
			$this->isInFirstLevelStatement(),
			$this->currentlyAssignedExpressions,
			$this->currentlyAllowedUndefinedExpressions,
			$this->inFunctionCallsStack,
			$this->afterExtractCall,
			$this->parentScope,
			$this->nativeTypesPromoted,
		);
	}

	public function afterOpenSslCall(string $openSslFunctionName): self
	{
		$expressionTypes = $this->expressionTypes;

		if (in_array($openSslFunctionName, [
			'openssl_cipher_iv_length',
			'openssl_cms_decrypt',
			'openssl_cms_encrypt',
			'openssl_cms_read',
			'openssl_cms_sign',
			'openssl_cms_verify',
			'openssl_csr_export_to_file',
			'openssl_csr_export',
			'openssl_csr_get_public_key',
			'openssl_csr_get_subject',
			'openssl_csr_new',
			'openssl_csr_sign',
			'openssl_decrypt',
			'openssl_dh_compute_key',
			'openssl_digest',
			'openssl_encrypt',
			'openssl_get_curve_names',
			'openssl_get_privatekey',
			'openssl_get_publickey',
			'openssl_open',
			'openssl_pbkdf2',
			'openssl_pkcs12_export_to_file',
			'openssl_pkcs12_export',
			'openssl_pkcs12_read',
			'openssl_pkcs7_decrypt',
			'openssl_pkcs7_encrypt',
			'openssl_pkcs7_read',
			'openssl_pkcs7_sign',
			'openssl_pkcs7_verify',
			'openssl_pkey_derive',
			'openssl_pkey_export_to_file',
			'openssl_pkey_export',
			'openssl_pkey_get_private',
			'openssl_pkey_get_public',
			'openssl_pkey_new',
			'openssl_private_decrypt',
			'openssl_private_encrypt',
			'openssl_public_decrypt',
			'openssl_public_encrypt',
			'openssl_random_pseudo_bytes',
			'openssl_seal',
			'openssl_sign',
			'openssl_spki_export_challenge',
			'openssl_spki_export',
			'openssl_spki_new',
			'openssl_spki_verify',
			'openssl_verify',
			'openssl_x509_checkpurpose',
			'openssl_x509_export_to_file',
			'openssl_x509_export',
			'openssl_x509_fingerprint',
			'openssl_x509_read',
			'openssl_x509_verify',
		], true)) {
			unset($expressionTypes['\openssl_error_string()']);
		}

		return $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$expressionTypes,
			$this->nativeExpressionTypes,
			$this->conditionalExpressions,
			$this->inClosureBindScopeClasses,
			$this->anonymousFunctionReflection,
			$this->isInFirstLevelStatement(),
			$this->currentlyAssignedExpressions,
			$this->currentlyAllowedUndefinedExpressions,
			$this->inFunctionCallsStack,
			$this->afterExtractCall,
			$this->parentScope,
			$this->nativeTypesPromoted,
		);
	}

	/** @api */
	public function hasVariableType(string $variableName): TrinaryLogic
	{
		if ($this->isGlobalVariable($variableName)) {
			return TrinaryLogic::createYes();
		}

		$varExprString = '$' . $variableName;
		if (!isset($this->expressionTypes[$varExprString])) {
			if ($this->canAnyVariableExist()) {
				return TrinaryLogic::createMaybe();
			}

			return TrinaryLogic::createNo();
		}

		return $this->expressionTypes[$varExprString]->getCertainty();
	}

	/** @api */
	public function getVariableType(string $variableName): Type
	{
		if ($this->hasVariableType($variableName)->maybe()) {
			if ($variableName === 'argc') {
				return IntegerRangeType::fromInterval(1, null);
			}
			if ($variableName === 'argv') {
				return AccessoryArrayListType::intersectWith(TypeCombinator::intersect(
					new ArrayType(new IntegerType(), new StringType()),
					new NonEmptyArrayType(),
				));
			}
			if ($this->canAnyVariableExist()) {
				return new MixedType();
			}
		}

		if ($this->isGlobalVariable($variableName)) {
			return new ArrayType(new StringType(), new MixedType($this->explicitMixedForGlobalVariables));
		}

		if ($this->hasVariableType($variableName)->no()) {
			throw new UndefinedVariableException($this, $variableName);
		}

		$varExprString = '$' . $variableName;
		if (!array_key_exists($varExprString, $this->expressionTypes)) {
			return new MixedType();
		}

		return TypeUtils::resolveLateResolvableTypes($this->expressionTypes[$varExprString]->getType());
	}

	/**
	 * @api
	 * @return array<int, string>
	 */
	public function getDefinedVariables(): array
	{
		$variables = [];
		foreach ($this->expressionTypes as $exprString => $holder) {
			if (!$holder->getExpr() instanceof Variable) {
				continue;
			}
			if (!$holder->getCertainty()->yes()) {
				continue;
			}

			$variables[] = substr($exprString, 1);
		}

		return $variables;
	}

	private function isGlobalVariable(string $variableName): bool
	{
		return in_array($variableName, [
			'GLOBALS',
			'_SERVER',
			'_GET',
			'_POST',
			'_FILES',
			'_COOKIE',
			'_SESSION',
			'_REQUEST',
			'_ENV',
		], true);
	}

	/** @api */
	public function hasConstant(Name $name): bool
	{
		$isCompilerHaltOffset = $name->toString() === '__COMPILER_HALT_OFFSET__';
		if ($isCompilerHaltOffset) {
			return $this->fileHasCompilerHaltStatementCalls();
		}

		if (!$name->isFullyQualified() && $this->getNamespace() !== null) {
			if ($this->hasExpressionType(new ConstFetch(new FullyQualified([$this->getNamespace(), $name->toString()])))->yes()) {
				return true;
			}
		}
		if ($this->hasExpressionType(new ConstFetch(new FullyQualified($name->toString())))->yes()) {
			return true;
		}

		return $this->reflectionProvider->hasConstant($name, $this);
	}

	private function fileHasCompilerHaltStatementCalls(): bool
	{
		$nodes = $this->parser->parseFile($this->getFile());
		foreach ($nodes as $node) {
			if ($node instanceof Node\Stmt\HaltCompiler) {
				return true;
			}
		}

		return false;
	}

	/** @api */
	public function isInAnonymousFunction(): bool
	{
		return $this->anonymousFunctionReflection !== null;
	}

	/** @api */
	public function getAnonymousFunctionReflection(): ?ParametersAcceptor
	{
		return $this->anonymousFunctionReflection;
	}

	/** @api */
	public function getAnonymousFunctionReturnType(): ?Type
	{
		if ($this->anonymousFunctionReflection === null) {
			return null;
		}

		return $this->anonymousFunctionReflection->getReturnType();
	}

	/** @api */
	public function getType(Expr $node): Type
	{
		if ($node instanceof GetIterableKeyTypeExpr) {
			return $this->getType($node->getExpr())->getIterableKeyType();
		}
		if ($node instanceof GetIterableValueTypeExpr) {
			return $this->getType($node->getExpr())->getIterableValueType();
		}
		if ($node instanceof GetOffsetValueTypeExpr) {
			return $this->getType($node->getVar())->getOffsetValueType($this->getType($node->getDim()));
		}
		if ($node instanceof SetOffsetValueTypeExpr) {
			return $this->getType($node->getVar())->setOffsetValueType(
				$node->getDim() !== null ? $this->getType($node->getDim()) : null,
				$this->getType($node->getValue()),
			);
		}
		if ($node instanceof TypeExpr) {
			return $node->getExprType();
		}

		if ($node instanceof OriginalPropertyTypeExpr) {
			$propertyReflection = $this->propertyReflectionFinder->findPropertyReflectionFromNode($node->getPropertyFetch(), $this);
			if ($propertyReflection === null) {
				return new ErrorType();
			}

			return $propertyReflection->getReadableType();
		}

		$key = $this->getNodeKey($node);

		if (!array_key_exists($key, $this->resolvedTypes)) {
			$this->resolvedTypes[$key] = TypeUtils::resolveLateResolvableTypes($this->resolveType($key, $node));
		}
		return $this->resolvedTypes[$key];
	}

	private function getNodeKey(Expr $node): string
	{
		$key = $this->exprPrinter->printExpr($node);

		if (
			$node instanceof Node\FunctionLike
			&& $node->hasAttribute(ArrayMapArgVisitor::ATTRIBUTE_NAME)
			&& $node->hasAttribute('startFilePos')
		) {
			$key .= '/*' . $node->getAttribute('startFilePos') . '*/';
		}

		return $key;
	}

	private function resolveType(string $exprString, Expr $node): Type
	{
		if ($node instanceof Expr\Exit_ || $node instanceof Expr\Throw_) {
			return new NeverType(true);
		}

		if (!$node instanceof Variable && $this->hasExpressionType($node)->yes()) {
			return $this->expressionTypes[$exprString]->getType();
		}

		if ($node instanceof Expr\BinaryOp\Smaller) {
			return $this->getType($node->left)->isSmallerThan($this->getType($node->right))->toBooleanType();
		}

		if ($node instanceof Expr\BinaryOp\SmallerOrEqual) {
			return $this->getType($node->left)->isSmallerThanOrEqual($this->getType($node->right))->toBooleanType();
		}

		if ($node instanceof Expr\BinaryOp\Greater) {
			return $this->getType($node->right)->isSmallerThan($this->getType($node->left))->toBooleanType();
		}

		if ($node instanceof Expr\BinaryOp\GreaterOrEqual) {
			return $this->getType($node->right)->isSmallerThanOrEqual($this->getType($node->left))->toBooleanType();
		}

		if ($node instanceof Expr\BinaryOp\Equal) {
			if (
				$node->left instanceof Variable
				&& is_string($node->left->name)
				&& $node->right instanceof Variable
				&& is_string($node->right->name)
				&& $node->left->name === $node->right->name
			) {
				return new ConstantBooleanType(true);
			}

			$leftType = $this->getType($node->left);
			$rightType = $this->getType($node->right);

			return $this->initializerExprTypeResolver->resolveEqualType($leftType, $rightType);
		}

		if ($node instanceof Expr\BinaryOp\NotEqual) {
			return $this->getType(new Expr\BooleanNot(new BinaryOp\Equal($node->left, $node->right)));
		}

		if ($node instanceof Expr\Empty_) {
			$result = $this->issetCheck($node->expr, static function (Type $type): ?bool {
				$isNull = (new NullType())->isSuperTypeOf($type);
				$isFalsey = (new ConstantBooleanType(false))->isSuperTypeOf($type->toBoolean());
				if ($isNull->maybe()) {
					return null;
				}
				if ($isFalsey->maybe()) {
					return null;
				}

				if ($isNull->yes()) {
					if ($isFalsey->yes()) {
						return false;
					}
					if ($isFalsey->no()) {
						return true;
					}

					return false;
				}

				return !$isFalsey->yes();
			});
			if ($result === null) {
				return new BooleanType();
			}

			return new ConstantBooleanType(!$result);
		}

		if ($node instanceof Node\Expr\BooleanNot) {
			$exprBooleanType = $this->getType($node->expr)->toBoolean();
			if ($exprBooleanType instanceof ConstantBooleanType) {
				return new ConstantBooleanType(!$exprBooleanType->getValue());
			}

			return new BooleanType();
		}

		if ($node instanceof Node\Expr\BitwiseNot) {
			return $this->initializerExprTypeResolver->getBitwiseNotType($node->expr, fn (Expr $expr): Type => $this->getType($expr));
		}

		if (
			$node instanceof Node\Expr\BinaryOp\BooleanAnd
			|| $node instanceof Node\Expr\BinaryOp\LogicalAnd
		) {
			$leftBooleanType = $this->getType($node->left)->toBoolean();
			if (
				$leftBooleanType->isFalse()->yes()
			) {
				return new ConstantBooleanType(false);
			}

			$rightBooleanType = $this->filterByTruthyValue($node->left)->getType($node->right)->toBoolean();

			if (
				$rightBooleanType->isFalse()->yes()
			) {
				return new ConstantBooleanType(false);
			}

			if (
				$leftBooleanType->isTrue()->yes()
				&& $rightBooleanType->isTrue()->yes()
			) {
				return new ConstantBooleanType(true);
			}

			return new BooleanType();
		}

		if (
			$node instanceof Node\Expr\BinaryOp\BooleanOr
			|| $node instanceof Node\Expr\BinaryOp\LogicalOr
		) {
			$leftBooleanType = $this->getType($node->left)->toBoolean();
			if (
				$leftBooleanType->isTrue()->yes()
			) {
				return new ConstantBooleanType(true);
			}

			$rightBooleanType = $this->filterByFalseyValue($node->left)->getType($node->right)->toBoolean();

			if (
				$rightBooleanType->isTrue()->yes()
			) {
				return new ConstantBooleanType(true);
			}

			if (
				$leftBooleanType->isFalse()->yes()
				&& $rightBooleanType->isFalse()->yes()
			) {
				return new ConstantBooleanType(false);
			}

			return new BooleanType();
		}

		if ($node instanceof Node\Expr\BinaryOp\LogicalXor) {
			$leftBooleanType = $this->getType($node->left)->toBoolean();
			$rightBooleanType = $this->getType($node->right)->toBoolean();

			if (
				$leftBooleanType instanceof ConstantBooleanType
				&& $rightBooleanType instanceof ConstantBooleanType
			) {
				return new ConstantBooleanType(
					$leftBooleanType->getValue() xor $rightBooleanType->getValue(),
				);
			}

			return new BooleanType();
		}

		if ($node instanceof Expr\BinaryOp\Identical) {
			if (
				$node->left instanceof Variable
				&& is_string($node->left->name)
				&& $node->right instanceof Variable
				&& is_string($node->right->name)
				&& $node->left->name === $node->right->name
			) {
				return new ConstantBooleanType(true);
			}

			$leftType = $this->getType($node->left);
			$rightType = $this->getType($node->right);

			if (
				(
					$node->left instanceof Node\Expr\PropertyFetch
					|| $node->left instanceof Node\Expr\StaticPropertyFetch
				)
				&& $rightType->isNull()->yes()
				&& !$this->hasPropertyNativeType($node->left)
			) {
				return new BooleanType();
			}

			if (
				(
					$node->right instanceof Node\Expr\PropertyFetch
					|| $node->right instanceof Node\Expr\StaticPropertyFetch
				)
				&& $leftType->isNull()->yes()
				&& !$this->hasPropertyNativeType($node->right)
			) {
				return new BooleanType();
			}

			return $this->initializerExprTypeResolver->resolveIdenticalType($leftType, $rightType);
		}

		if ($node instanceof Expr\BinaryOp\NotIdentical) {
			return $this->getType(new Expr\BooleanNot(new BinaryOp\Identical($node->left, $node->right)));
		}

		if ($node instanceof Expr\Instanceof_) {
			$expressionType = $this->getType($node->expr);
			if (
				$this->isInTrait()
				&& TypeUtils::findThisType($expressionType) !== null
			) {
				return new BooleanType();
			}
			if ($expressionType instanceof NeverType) {
				return new ConstantBooleanType(false);
			}

			$uncertainty = false;

			if ($node->class instanceof Node\Name) {
				$unresolvedClassName = $node->class->toString();
				if (
					strtolower($unresolvedClassName) === 'static'
					&& $this->isInClass()
				) {
					$classType = new StaticType($this->getClassReflection());
				} else {
					$className = $this->resolveName($node->class);
					$classType = new ObjectType($className);
				}
			} else {
				$classType = $this->getType($node->class);
				$classType = TypeTraverser::map($classType, static function (Type $type, callable $traverse) use (&$uncertainty): Type {
					if ($type instanceof UnionType || $type instanceof IntersectionType) {
						return $traverse($type);
					}
					if ($type->getObjectClassNames() !== []) {
						$uncertainty = true;
						return $type;
					}
					if ($type instanceof GenericClassStringType) {
						$uncertainty = true;
						return $type->getGenericType();
					}
					if ($type instanceof ConstantStringType) {
						return new ObjectType($type->getValue());
					}
					return new MixedType();
				});
			}

			if ($classType->isSuperTypeOf(new MixedType())->yes()) {
				return new BooleanType();
			}

			$isSuperType = $classType->isSuperTypeOf($expressionType);

			if ($isSuperType->no()) {
				return new ConstantBooleanType(false);
			} elseif ($isSuperType->yes() && !$uncertainty) {
				return new ConstantBooleanType(true);
			}

			return new BooleanType();
		}

		if ($node instanceof Node\Expr\UnaryPlus) {
			return $this->getType($node->expr)->toNumber();
		}

		if ($node instanceof Expr\ErrorSuppress
			|| $node instanceof Expr\Assign
		) {
			return $this->getType($node->expr);
		}

		if ($node instanceof Node\Expr\UnaryMinus) {
			return $this->initializerExprTypeResolver->getUnaryMinusType($node->expr, fn (Expr $expr): Type => $this->getType($expr));
		}

		if ($node instanceof Expr\BinaryOp\Concat) {
			return $this->initializerExprTypeResolver->getConcatType($node->left, $node->right, fn (Expr $expr): Type => $this->getType($expr));
		}

		if ($node instanceof Expr\AssignOp\Concat) {
			return $this->initializerExprTypeResolver->getConcatType($node->var, $node->expr, fn (Expr $expr): Type => $this->getType($expr));
		}

		if ($node instanceof BinaryOp\BitwiseAnd) {
			return $this->initializerExprTypeResolver->getBitwiseAndType($node->left, $node->right, fn (Expr $expr): Type => $this->getType($expr));
		}

		if ($node instanceof Expr\AssignOp\BitwiseAnd) {
			return $this->initializerExprTypeResolver->getBitwiseAndType($node->var, $node->expr, fn (Expr $expr): Type => $this->getType($expr));
		}

		if ($node instanceof BinaryOp\BitwiseOr) {
			return $this->initializerExprTypeResolver->getBitwiseOrType($node->left, $node->right, fn (Expr $expr): Type => $this->getType($expr));
		}

		if ($node instanceof Expr\AssignOp\BitwiseOr) {
			return $this->initializerExprTypeResolver->getBitwiseOrType($node->var, $node->expr, fn (Expr $expr): Type => $this->getType($expr));
		}

		if ($node instanceof BinaryOp\BitwiseXor) {
			return $this->initializerExprTypeResolver->getBitwiseXorType($node->left, $node->right, fn (Expr $expr): Type => $this->getType($expr));
		}

		if ($node instanceof Expr\AssignOp\BitwiseXor) {
			return $this->initializerExprTypeResolver->getBitwiseXorType($node->var, $node->expr, fn (Expr $expr): Type => $this->getType($expr));
		}

		if ($node instanceof Expr\BinaryOp\Spaceship) {
			return $this->initializerExprTypeResolver->getSpaceshipType($node->left, $node->right, fn (Expr $expr): Type => $this->getType($expr));
		}

		if ($node instanceof BinaryOp\Div) {
			return $this->initializerExprTypeResolver->getDivType($node->left, $node->right, fn (Expr $expr): Type => $this->getType($expr));
		}

		if ($node instanceof Expr\AssignOp\Div) {
			return $this->initializerExprTypeResolver->getDivType($node->var, $node->expr, fn (Expr $expr): Type => $this->getType($expr));
		}

		if ($node instanceof BinaryOp\Mod) {
			return $this->initializerExprTypeResolver->getModType($node->left, $node->right, fn (Expr $expr): Type => $this->getType($expr));
		}

		if ($node instanceof Expr\AssignOp\Mod) {
			return $this->initializerExprTypeResolver->getModType($node->var, $node->expr, fn (Expr $expr): Type => $this->getType($expr));
		}

		if ($node instanceof BinaryOp\Plus) {
			return $this->initializerExprTypeResolver->getPlusType($node->left, $node->right, fn (Expr $expr): Type => $this->getType($expr));
		}

		if ($node instanceof Expr\AssignOp\Plus) {
			return $this->initializerExprTypeResolver->getPlusType($node->var, $node->expr, fn (Expr $expr): Type => $this->getType($expr));
		}

		if ($node instanceof BinaryOp\Minus) {
			return $this->initializerExprTypeResolver->getMinusType($node->left, $node->right, fn (Expr $expr): Type => $this->getType($expr));
		}

		if ($node instanceof Expr\AssignOp\Minus) {
			return $this->initializerExprTypeResolver->getMinusType($node->var, $node->expr, fn (Expr $expr): Type => $this->getType($expr));
		}

		if ($node instanceof BinaryOp\Mul) {
			return $this->initializerExprTypeResolver->getMulType($node->left, $node->right, fn (Expr $expr): Type => $this->getType($expr));
		}

		if ($node instanceof Expr\AssignOp\Mul) {
			return $this->initializerExprTypeResolver->getMulType($node->var, $node->expr, fn (Expr $expr): Type => $this->getType($expr));
		}

		if ($node instanceof BinaryOp\Pow) {
			return $this->initializerExprTypeResolver->getPowType($node->left, $node->right, fn (Expr $expr): Type => $this->getType($expr));
		}

		if ($node instanceof Expr\AssignOp\Pow) {
			return $this->initializerExprTypeResolver->getPowType($node->var, $node->expr, fn (Expr $expr): Type => $this->getType($expr));
		}

		if ($node instanceof BinaryOp\ShiftLeft) {
			return $this->initializerExprTypeResolver->getShiftLeftType($node->left, $node->right, fn (Expr $expr): Type => $this->getType($expr));
		}

		if ($node instanceof Expr\AssignOp\ShiftLeft) {
			return $this->initializerExprTypeResolver->getShiftLeftType($node->var, $node->expr, fn (Expr $expr): Type => $this->getType($expr));
		}

		if ($node instanceof BinaryOp\ShiftRight) {
			return $this->initializerExprTypeResolver->getShiftRightType($node->left, $node->right, fn (Expr $expr): Type => $this->getType($expr));
		}

		if ($node instanceof Expr\AssignOp\ShiftRight) {
			return $this->initializerExprTypeResolver->getShiftRightType($node->var, $node->expr, fn (Expr $expr): Type => $this->getType($expr));
		}

		if ($node instanceof Expr\Clone_) {
			return $this->getType($node->expr);
		}

		if ($node instanceof LNumber) {
			return $this->initializerExprTypeResolver->getType($node, InitializerExprContext::fromScope($this));
		} elseif ($node instanceof String_) {
			return $this->initializerExprTypeResolver->getType($node, InitializerExprContext::fromScope($this));
		} elseif ($node instanceof Node\Scalar\Encapsed) {
			$resultType = null;

			foreach ($node->parts as $part) {
				$partType = $part instanceof EncapsedStringPart
					? new ConstantStringType($part->value)
					: $this->getType($part)->toString();
				if ($resultType === null) {
					$resultType = $partType;

					continue;
				}

				$resultType = $this->initializerExprTypeResolver->resolveConcatType($resultType, $partType);
				if (count($resultType->getConstantStrings()) === 0) {
					return $resultType;
				}
			}

			return $resultType ?? new ConstantStringType('');
		} elseif ($node instanceof DNumber) {
			return $this->initializerExprTypeResolver->getType($node, InitializerExprContext::fromScope($this));
		} elseif ($node instanceof Expr\CallLike && $node->isFirstClassCallable()) {
			if ($node instanceof FuncCall) {
				if ($node->name instanceof Name) {
					if ($this->reflectionProvider->hasFunction($node->name, $this)) {
						return $this->createFirstClassCallable(
							$this->reflectionProvider->getFunction($node->name, $this)->getVariants(),
						);
					}

					return new ObjectType(Closure::class);
				}

				$callableType = $this->getType($node->name);
				if (!$callableType->isCallable()->yes()) {
					return new ObjectType(Closure::class);
				}

				return $this->createFirstClassCallable(
					$callableType->getCallableParametersAcceptors($this),
				);
			}

			if ($node instanceof MethodCall) {
				if (!$node->name instanceof Node\Identifier) {
					return new ObjectType(Closure::class);
				}

				$varType = $this->getType($node->var);
				$method = $this->getMethodReflection($varType, $node->name->toString());
				if ($method === null) {
					return new ObjectType(Closure::class);
				}

				return $this->createFirstClassCallable($method->getVariants());
			}

			if ($node instanceof Expr\StaticCall) {
				if (!$node->class instanceof Name) {
					return new ObjectType(Closure::class);
				}

				$classType = $this->resolveTypeByName($node->class);
				if (!$node->name instanceof Node\Identifier) {
					return new ObjectType(Closure::class);
				}

				$methodName = $node->name->toString();
				if (!$classType->hasMethod($methodName)->yes()) {
					return new ObjectType(Closure::class);
				}

				return $this->createFirstClassCallable($classType->getMethod($methodName, $this)->getVariants());
			}

			if ($node instanceof New_) {
				return new ErrorType();
			}

			throw new ShouldNotHappenException();
		} elseif ($node instanceof Expr\Closure || $node instanceof Expr\ArrowFunction) {
			$parameters = [];
			$isVariadic = false;
            
			$firstOptionalParameterIndex = null;
			foreach ($node->params as $i => $param) {
				$isOptionalCandidate = $param->default !== null || $param->variadic;

				if ($isOptionalCandidate) {
					if ($firstOptionalParameterIndex === null) {
						$firstOptionalParameterIndex = $i;
					}
				} else {
					$firstOptionalParameterIndex = null;
				}
			}

			foreach ($node->params as $i => $param) {
				if ($param->variadic) {
					$isVariadic = true;
				}
				if (!$param->var instanceof Variable || !is_string($param->var->name)) {
					throw new ShouldNotHappenException();
				}
				$parameters[] = new NativeParameterReflection(
					$param->var->name,
					$firstOptionalParameterIndex !== null && $i >= $firstOptionalParameterIndex,
					$this->getFunctionType($param->type, $this->isParameterValueNullable($param), false),
					$param->byRef
						? PassedByReference::createCreatesNewVariable()
						: PassedByReference::createNo(),
					$param->variadic,
					$param->default !== null ? $this->getType($param->default) : null,
				);
			}

			$callableParameters = null;
			$arrayMapArgs = $node->getAttribute(ArrayMapArgVisitor::ATTRIBUTE_NAME);
			if ($arrayMapArgs !== null) {
				$callableParameters = [];
				foreach ($arrayMapArgs as $funcCallArg) {
					$callableParameters[] = new DummyParameter('item', $this->getType($funcCallArg->value)->getIterableValueType(), false, PassedByReference::createNo(), false, null);
				}
			}

			if ($node instanceof Expr\ArrowFunction) {
				$arrowScope = $this->enterArrowFunctionWithoutReflection($node, $callableParameters);

				if ($node->expr instanceof Expr\Yield_ || $node->expr instanceof Expr\YieldFrom) {
					$yieldNode = $node->expr;

					if ($yieldNode instanceof Expr\Yield_) {
						if ($yieldNode->key === null) {
							$keyType = new IntegerType();
						} else {
							$keyType = $arrowScope->getType($yieldNode->key);
						}

						if ($yieldNode->value === null) {
							$valueType = new NullType();
						} else {
							$valueType = $arrowScope->getType($yieldNode->value);
						}
					} else {
						$yieldFromType = $arrowScope->getType($yieldNode->expr);
						$keyType = $yieldFromType->getIterableKeyType();
						$valueType = $yieldFromType->getIterableValueType();
					}

					$returnType = new GenericObjectType(Generator::class, [
						$keyType,
						$valueType,
						new MixedType(),
						new VoidType(),
					]);
				} else {
					$returnType = $arrowScope->getType($node->expr);
					if ($node->returnType !== null) {
						$returnType = TypehintHelper::decideType($this->getFunctionType($node->returnType, false, false), $returnType);
					}
				}
			} else {
				$closureScope = $this->enterAnonymousFunctionWithoutReflection($node, $callableParameters);
				$closureReturnStatements = [];
				$closureYieldStatements = [];
				$closureExecutionEnds = [];
				$this->nodeScopeResolver->processStmtNodes($node, $node->stmts, $closureScope, static function (Node $node, Scope $scope) use ($closureScope, &$closureReturnStatements, &$closureYieldStatements, &$closureExecutionEnds): void {
					if ($scope->getAnonymousFunctionReflection() !== $closureScope->getAnonymousFunctionReflection()) {
						return;
					}

					if ($node instanceof ExecutionEndNode) {
						if ($node->getStatementResult()->isAlwaysTerminating()) {
							foreach ($node->getStatementResult()->getExitPoints() as $exitPoint) {
								if ($exitPoint->getStatement() instanceof Node\Stmt\Return_) {
									continue;
								}

								$closureExecutionEnds[] = $node;
								break;
							}

							if (count($node->getStatementResult()->getExitPoints()) === 0) {
								$closureExecutionEnds[] = $node;
							}
						}

						return;
					}

					if ($node instanceof Node\Stmt\Return_) {
						$closureReturnStatements[] = [$node, $scope];
					}

					if (!$node instanceof Expr\Yield_ && !$node instanceof Expr\YieldFrom) {
						return;
					}

					$closureYieldStatements[] = [$node, $scope];
				}, StatementContext::createTopLevel());

				$returnTypes = [];
				$hasNull = false;
				foreach ($closureReturnStatements as [$returnNode, $returnScope]) {
					if ($returnNode->expr === null) {
						$hasNull = true;
						continue;
					}

					$returnTypes[] = $returnScope->getType($returnNode->expr);
				}

				if (count($returnTypes) === 0) {
					if (count($closureExecutionEnds) > 0 && !$hasNull) {
						$returnType = new NeverType(true);
					} else {
						$returnType = new VoidType();
					}
				} else {
					if (count($closureExecutionEnds) > 0) {
						$returnTypes[] = new NeverType(true);
					}
					if ($hasNull) {
						$returnTypes[] = new NullType();
					}
					$returnType = TypeCombinator::union(...$returnTypes);
				}

				if (count($closureYieldStatements) > 0) {
					$keyTypes = [];
					$valueTypes = [];
					foreach ($closureYieldStatements as [$yieldNode, $yieldScope]) {
						if ($yieldNode instanceof Expr\Yield_) {
							if ($yieldNode->key === null) {
								$keyTypes[] = new IntegerType();
							} else {
								$keyTypes[] = $yieldScope->getType($yieldNode->key);
							}

							if ($yieldNode->value === null) {
								$valueTypes[] = new NullType();
							} else {
								$valueTypes[] = $yieldScope->getType($yieldNode->value);
							}

							continue;
						}

						$yieldFromType = $yieldScope->getType($yieldNode->expr);
						$keyTypes[] = $yieldFromType->getIterableKeyType();
						$valueTypes[] = $yieldFromType->getIterableValueType();
					}

					$returnType = new GenericObjectType(Generator::class, [
						TypeCombinator::union(...$keyTypes),
						TypeCombinator::union(...$valueTypes),
						new MixedType(),
						$returnType,
					]);
				} else {
					$returnType = TypehintHelper::decideType($this->getFunctionType($node->returnType, false, false), $returnType);
				}
			}

			return new ClosureType(
				$parameters,
				$returnType,
				$isVariadic,
			);
		} elseif ($node instanceof New_) {
			if ($node->class instanceof Name) {
				$type = $this->exactInstantiation($node, $node->class->toString());
				if ($type !== null) {
					return $type;
				}

				$lowercasedClassName = strtolower($node->class->toString());
				if ($lowercasedClassName === 'static') {
					if (!$this->isInClass()) {
						return new ErrorType();
					}

					return new StaticType($this->getClassReflection());
				}
				if ($lowercasedClassName === 'parent') {
					return new NonexistentParentClassType();
				}

				return new ObjectType($node->class->toString());
			}
			if ($node->class instanceof Node\Stmt\Class_) {
				$anonymousClassReflection = $this->reflectionProvider->getAnonymousClassReflection($node->class, $this);

				return new ObjectType($anonymousClassReflection->getName());
			}

			$exprType = $this->getType($node->class);
			return $exprType->getObjectTypeOrClassStringObjectType();

		} elseif ($node instanceof Array_) {
			return $this->initializerExprTypeResolver->getArrayType($node, fn (Expr $expr): Type => $this->getType($expr));
		} elseif ($node instanceof Int_) {
			return $this->getType($node->expr)->toInteger();
		} elseif ($node instanceof Bool_) {
			return $this->getType($node->expr)->toBoolean();
		} elseif ($node instanceof Double) {
			return $this->getType($node->expr)->toFloat();
		} elseif ($node instanceof Node\Expr\Cast\String_) {
			return $this->getType($node->expr)->toString();
		} elseif ($node instanceof Node\Expr\Cast\Array_) {
			return $this->getType($node->expr)->toArray();
		} elseif ($node instanceof Node\Scalar\MagicConst) {
			return $this->initializerExprTypeResolver->getType($node, InitializerExprContext::fromScope($this));
		} elseif ($node instanceof Object_) {
			$castToObject = static function (Type $type): Type {
				if ($type->isObject()->yes()) {
					return $type;
				}

				return new ObjectType('stdClass');
			};

			$exprType = $this->getType($node->expr);
			if ($exprType instanceof UnionType) {
				return TypeCombinator::union(...array_map($castToObject, $exprType->getTypes()));
			}

			return $castToObject($exprType);
		} elseif ($node instanceof Unset_) {
			return new NullType();
		} elseif ($node instanceof Expr\PostInc || $node instanceof Expr\PostDec) {
			return $this->getType($node->var);
		} elseif ($node instanceof Expr\PreInc || $node instanceof Expr\PreDec) {
			$varType = $this->getType($node->var);
			$varScalars = $varType->getConstantScalarValues();
			$stringType = new StringType();
			if (count($varScalars) > 0) {
				$newTypes = [];

				foreach ($varScalars as $varValue) {
					if ($node instanceof Expr\PreInc) {
						++$varValue;
					} else {
						--$varValue;
					}

					$newTypes[] = $this->getTypeFromValue($varValue);
				}
				return TypeCombinator::union(...$newTypes);
			} elseif ($varType->isString()->yes()) {
				if ($varType->isLiteralString()->yes()) {
					return new IntersectionType([$stringType, new AccessoryLiteralStringType()]);
				}
				return $stringType;
			}

			if ($node instanceof Expr\PreInc) {
				return $this->getType(new BinaryOp\Plus($node->var, new LNumber(1)));
			}

			return $this->getType(new BinaryOp\Minus($node->var, new LNumber(1)));
		} elseif ($node instanceof Expr\Yield_) {
			$functionReflection = $this->getFunction();
			if ($functionReflection === null) {
				return new MixedType();
			}

			$returnType = ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();
			$generatorSendType = $returnType->getTemplateType(Generator::class, 'TSend');
			if ($generatorSendType instanceof ErrorType) {
				return new MixedType();
			}

			return $generatorSendType;
		} elseif ($node instanceof Expr\YieldFrom) {
			$yieldFromType = $this->getType($node->expr);
			$generatorReturnType = $yieldFromType->getTemplateType(Generator::class, 'TReturn');
			if ($generatorReturnType instanceof ErrorType) {
				return new MixedType();
			}

			return $generatorReturnType;
		} elseif ($node instanceof Expr\Match_) {
			$cond = $node->cond;
			$types = [];

			$matchScope = $this;
			foreach ($node->arms as $arm) {
				if ($arm->conds === null) {
					$types[] = $matchScope->getType($arm->body);
					continue;
				}

				if (count($arm->conds) === 0) {
					throw new ShouldNotHappenException();
				}

				$filteringExpr = null;
				foreach ($arm->conds as $armCond) {
					$armCondExpr = new BinaryOp\Identical($cond, $armCond);

					if ($filteringExpr === null) {
						$filteringExpr = $armCondExpr;
						continue;
					}

					$filteringExpr = new BinaryOp\BooleanOr($filteringExpr, $armCondExpr);
				}

				$filteringExprType = $matchScope->getType($filteringExpr);

				if (!(new ConstantBooleanType(false))->isSuperTypeOf($filteringExprType)->yes()) {
					$truthyScope = $matchScope->filterByTruthyValue($filteringExpr);
					$types[] = $truthyScope->getType($arm->body);
				}

				$matchScope = $matchScope->filterByFalseyValue($filteringExpr);
			}

			return TypeCombinator::union(...$types);
		}

		if ($node instanceof Expr\Isset_) {
			$issetResult = true;
			foreach ($node->vars as $var) {
				$result = $this->issetCheck($var, static function (Type $type): ?bool {
					$isNull = (new NullType())->isSuperTypeOf($type);
					if ($isNull->maybe()) {
						return null;
					}

					return !$isNull->yes();
				});
				if ($result !== null) {
					if (!$result) {
						return new ConstantBooleanType($result);
					}

					continue;
				}

				$issetResult = $result;
			}

			if ($issetResult === null) {
				return new BooleanType();
			}

			return new ConstantBooleanType($issetResult);
		}

		if ($node instanceof Expr\AssignOp\Coalesce) {
			return $this->getType(new BinaryOp\Coalesce($node->var, $node->expr, $node->getAttributes()));
		}

		if ($node instanceof Expr\BinaryOp\Coalesce) {
			$leftType = $this->getType($node->left);

			$result = $this->issetCheck($node->left, static function (Type $type): ?bool {
				$isNull = (new NullType())->isSuperTypeOf($type);
				if ($isNull->maybe()) {
					return null;
				}

				return !$isNull->yes();
			});

			if ($result !== null && $result !== false) {
				return TypeCombinator::removeNull($leftType);
			}

			$rightType = $this->filterByFalseyValue(
				new BinaryOp\NotIdentical($node->left, new ConstFetch(new Name('null'))),
			)->getType($node->right);

			if ($result === null) {
				return TypeCombinator::union(
					TypeCombinator::removeNull($leftType),
					$rightType,
				);
			}

			return $rightType;
		}

		if ($node instanceof ConstFetch) {
			$constName = (string) $node->name;
			$loweredConstName = strtolower($constName);
			if ($loweredConstName === 'true') {
				return new ConstantBooleanType(true);
			} elseif ($loweredConstName === 'false') {
				return new ConstantBooleanType(false);
			} elseif ($loweredConstName === 'null') {
				return new NullType();
			}

			$namespacedName = null;
			if (!$node->name->isFullyQualified() && $this->getNamespace() !== null) {
				$namespacedName = new FullyQualified([$this->getNamespace(), $node->name->toString()]);
			}
			$globalName = new FullyQualified($node->name->toString());

			foreach ([$namespacedName, $globalName] as $name) {
				if ($name === null) {
					continue;
				}
				$constFetch = new ConstFetch($name);
				if ($this->hasExpressionType($constFetch)->yes()) {
					return $this->constantResolver->resolveConstantType(
						$name->toString(),
						$this->expressionTypes[$this->getNodeKey($constFetch)]->getType(),
					);
				}
			}

			$constantType = $this->constantResolver->resolveConstant($node->name, $this);
			if ($constantType !== null) {
				return $constantType;
			}

			return new ErrorType();
		} elseif ($node instanceof Node\Expr\ClassConstFetch && $node->name instanceof Node\Identifier) {
			if ($this->hasExpressionType($node)->yes()) {
				return $this->expressionTypes[$exprString]->getType();
			}
			return $this->initializerExprTypeResolver->getClassConstFetchTypeByReflection(
				$node->class,
				$node->name->name,
				$this->isInClass() ? $this->getClassReflection() : null,
				fn (Expr $expr): Type => $this->getType($expr),
			);
		}

		if ($node instanceof Expr\Ternary) {
			if ($node->if === null) {
				$conditionType = $this->getType($node->cond);
				$booleanConditionType = $conditionType->toBoolean();
				if ($booleanConditionType->isTrue()->yes()) {
					return $this->filterByTruthyValue($node->cond)->getType($node->cond);
				}

				if ($booleanConditionType->isFalse()->yes()) {
					return $this->filterByFalseyValue($node->cond)->getType($node->else);
				}

				return TypeCombinator::union(
					TypeCombinator::removeFalsey($this->filterByTruthyValue($node->cond)->getType($node->cond)),
					$this->filterByFalseyValue($node->cond)->getType($node->else),
				);
			}

			$booleanConditionType = $this->getType($node->cond)->toBoolean();
			if ($booleanConditionType->isTrue()->yes()) {
				return $this->filterByTruthyValue($node->cond)->getType($node->if);
			}

			if ($booleanConditionType->isFalse()->yes()) {
				return $this->filterByFalseyValue($node->cond)->getType($node->else);
			}

			return TypeCombinator::union(
				$this->filterByTruthyValue($node->cond)->getType($node->if),
				$this->filterByFalseyValue($node->cond)->getType($node->else),
			);
		}

		if ($node instanceof Variable && is_string($node->name)) {
			if ($this->hasVariableType($node->name)->no()) {
				return new ErrorType();
			}

			return $this->getVariableType($node->name);
		}

		if ($node instanceof Expr\ArrayDimFetch && $node->dim !== null) {
			return $this->getNullsafeShortCircuitingType(
				$node->var,
				$this->getTypeFromArrayDimFetch(
					$node,
					$this->getType($node->dim),
					$this->getType($node->var),
				),
			);
		}

		if ($node instanceof MethodCall && $node->name instanceof Node\Identifier) {
			if ($this->nativeTypesPromoted) {
				$typeCallback = function () use ($node): Type {
					$methodReflection = $this->getMethodReflection(
						$this->getNativeType($node->var),
						$node->name->name,
					);
					if ($methodReflection === null) {
						return new ErrorType();
					}

					return ParametersAcceptorSelector::combineAcceptors($methodReflection->getVariants())->getNativeReturnType();
				};

				return $this->getNullsafeShortCircuitingType($node->var, $typeCallback());
			}

			$typeCallback = function () use ($node): Type {
				$returnType = $this->methodCallReturnType(
					$this->getType($node->var),
					$node->name->name,
					$node,
				);
				if ($returnType === null) {
					return new ErrorType();
				}
				return $returnType;
			};

			return $this->getNullsafeShortCircuitingType($node->var, $typeCallback());
		}

		if ($node instanceof Expr\NullsafeMethodCall) {
			$varType = $this->getType($node->var);
			if (!TypeCombinator::containsNull($varType)) {
				return $this->getType(new MethodCall($node->var, $node->name, $node->args));
			}

			return TypeCombinator::union(
				$this->filterByTruthyValue(new BinaryOp\NotIdentical($node->var, new ConstFetch(new Name('null'))))
					->getType(new MethodCall($node->var, $node->name, $node->args)),
				new NullType(),
			);
		}

		if ($node instanceof Expr\StaticCall && $node->name instanceof Node\Identifier) {
			if ($this->nativeTypesPromoted) {
				$typeCallback = function () use ($node): Type {
					if ($node->class instanceof Name) {
						$staticMethodCalledOnType = $this->resolveTypeByName($node->class);
					} else {
						$staticMethodCalledOnType = $this->getNativeType($node->class);
					}
					$methodReflection = $this->getMethodReflection(
						$staticMethodCalledOnType,
						$node->name->name,
					);
					if ($methodReflection === null) {
						return new ErrorType();
					}

					return ParametersAcceptorSelector::combineAcceptors($methodReflection->getVariants())->getNativeReturnType();
				};

				$callType = $typeCallback();
				if ($node->class instanceof Expr) {
					return $this->getNullsafeShortCircuitingType($node->class, $callType);
				}

				return $callType;
			}

			$typeCallback = function () use ($node): Type {
				if ($node->class instanceof Name) {
					$staticMethodCalledOnType = $this->resolveTypeByName($node->class);
				} else {
					$staticMethodCalledOnType = $this->getType($node->class)->getObjectTypeOrClassStringObjectType();
				}

				$returnType = $this->methodCallReturnType(
					$staticMethodCalledOnType,
					$node->name->toString(),
					$node,
				);
				if ($returnType === null) {
					return new ErrorType();
				}
				return $returnType;
			};

			$callType = $typeCallback();
			if ($node->class instanceof Expr) {
				return $this->getNullsafeShortCircuitingType($node->class, $callType);
			}

			return $callType;
		}

		if ($node instanceof PropertyFetch && $node->name instanceof Node\Identifier) {
			if ($this->nativeTypesPromoted) {
				$propertyReflection = $this->propertyReflectionFinder->findPropertyReflectionFromNode($node, $this);
				if ($propertyReflection === null) {
					return new ErrorType();
				}
				$nativeType = $propertyReflection->getNativeType();
				if ($nativeType === null) {
					return new ErrorType();
				}

				return $this->getNullsafeShortCircuitingType($node->var, $nativeType);
			}

			$typeCallback = function () use ($node): Type {
				$returnType = $this->propertyFetchType(
					$this->getType($node->var),
					$node->name->name,
					$node,
				);
				if ($returnType === null) {
					return new ErrorType();
				}
				return $returnType;
			};

			return $this->getNullsafeShortCircuitingType($node->var, $typeCallback());
		}

		if ($node instanceof Expr\NullsafePropertyFetch) {
			$varType = $this->getType($node->var);
			if (!TypeCombinator::containsNull($varType)) {
				return $this->getType(new PropertyFetch($node->var, $node->name));
			}

			return TypeCombinator::union(
				$this->filterByTruthyValue(new BinaryOp\NotIdentical($node->var, new ConstFetch(new Name('null'))))
					->getType(new PropertyFetch($node->var, $node->name)),
				new NullType(),
			);
		}

		if (
			$node instanceof Expr\StaticPropertyFetch
			&& $node->name instanceof Node\VarLikeIdentifier
		) {
			if ($this->nativeTypesPromoted) {
				$propertyReflection = $this->propertyReflectionFinder->findPropertyReflectionFromNode($node, $this);
				if ($propertyReflection === null) {
					return new ErrorType();
				}
				$nativeType = $propertyReflection->getNativeType();
				if ($nativeType === null) {
					return new ErrorType();
				}

				if ($node->class instanceof Expr) {
					return $this->getNullsafeShortCircuitingType($node->class, $nativeType);
				}

				return $nativeType;
			}

			$typeCallback = function () use ($node): Type {
				if ($node->class instanceof Name) {
					$staticPropertyFetchedOnType = $this->resolveTypeByName($node->class);
				} else {
					$staticPropertyFetchedOnType = $this->getType($node->class)->getObjectTypeOrClassStringObjectType();
				}

				$returnType = $this->propertyFetchType(
					$staticPropertyFetchedOnType,
					$node->name->toString(),
					$node,
				);
				if ($returnType === null) {
					return new ErrorType();
				}
				return $returnType;
			};

			$fetchType = $typeCallback();
			if ($node->class instanceof Expr) {
				return $this->getNullsafeShortCircuitingType($node->class, $fetchType);
			}

			return $fetchType;
		}

		if ($node instanceof FuncCall) {
			if ($node->name instanceof Expr) {
				$calledOnType = $this->getType($node->name);
				if ($calledOnType->isCallable()->no()) {
					return new ErrorType();
				}

				return ParametersAcceptorSelector::selectFromArgs(
					$this,
					$node->getArgs(),
					$calledOnType->getCallableParametersAcceptors($this),
				)->getReturnType();
			}

			if (!$this->reflectionProvider->hasFunction($node->name, $this)) {
				return new ErrorType();
			}

			$functionReflection = $this->reflectionProvider->getFunction($node->name, $this);
			if ($this->nativeTypesPromoted) {
				return ParametersAcceptorSelector::combineAcceptors($functionReflection->getVariants())->getNativeReturnType();
			}

			$parametersAcceptor = ParametersAcceptorSelector::selectFromArgs(
				$this,
				$node->getArgs(),
				$functionReflection->getVariants(),
			);
			$normalizedNode = ArgumentsNormalizer::reorderFuncArguments($parametersAcceptor, $node);
			if ($normalizedNode !== null) {
				foreach ($this->dynamicReturnTypeExtensionRegistry->getDynamicFunctionReturnTypeExtensions() as $dynamicFunctionReturnTypeExtension) {
					if (!$dynamicFunctionReturnTypeExtension->isFunctionSupported($functionReflection)) {
						continue;
					}

					$resolvedType = $dynamicFunctionReturnTypeExtension->getTypeFromFunctionCall(
						$functionReflection,
						$normalizedNode,
						$this,
					);
					if ($resolvedType !== null) {
						return $resolvedType;
					}
				}
			}

			return $parametersAcceptor->getReturnType();
		}

		return new MixedType();
	}

	private function getNullsafeShortCircuitingType(Expr $expr, Type $type): Type
	{
		if ($expr instanceof Expr\NullsafePropertyFetch || $expr instanceof Expr\NullsafeMethodCall) {
			$varType = $this->getType($expr->var);
			if (TypeCombinator::containsNull($varType)) {
				return TypeCombinator::addNull($type);
			}

			return $type;
		}

		if ($expr instanceof Expr\ArrayDimFetch) {
			return $this->getNullsafeShortCircuitingType($expr->var, $type);
		}

		if ($expr instanceof PropertyFetch) {
			return $this->getNullsafeShortCircuitingType($expr->var, $type);
		}

		if ($expr instanceof Expr\StaticPropertyFetch && $expr->class instanceof Expr) {
			return $this->getNullsafeShortCircuitingType($expr->class, $type);
		}

		if ($expr instanceof MethodCall) {
			return $this->getNullsafeShortCircuitingType($expr->var, $type);
		}

		if ($expr instanceof Expr\StaticCall && $expr->class instanceof Expr) {
			return $this->getNullsafeShortCircuitingType($expr->class, $type);
		}

		return $type;
	}

	/**
	 * @param callable(Type): ?bool $typeCallback
	 */
	public function issetCheck(Expr $expr, callable $typeCallback, ?bool $result = null): ?bool
	{
		// mirrored in PHPStan\Rules\IssetCheck
		if ($expr instanceof Node\Expr\Variable && is_string($expr->name)) {
			$hasVariable = $this->hasVariableType($expr->name);
			if ($hasVariable->maybe()) {
				return null;
			}

			if ($result === null) {
				if ($hasVariable->yes()) {
					if ($expr->name === '_SESSION') {
						return null;
					}

					return $typeCallback($this->getVariableType($expr->name));
				}

				return false;
			}

			return $result;
		} elseif ($expr instanceof Node\Expr\ArrayDimFetch && $expr->dim !== null) {
			$type = $this->getType($expr->var);
			$dimType = $this->getType($expr->dim);
			$hasOffsetValue = $type->hasOffsetValueType($dimType);
			if (!$type->isOffsetAccessible()->yes()) {
				return $result ?? $this->issetCheckUndefined($expr->var);
			}

			if ($hasOffsetValue->no()) {
				return false;
			}

			// If offset is cannot be null, store this error message and see if one of the earlier offsets is.
			// E.g. $array['a']['b']['c'] ?? null; is a valid coalesce if a OR b or C might be null.
			if ($hasOffsetValue->yes()) {
				$result = $typeCallback($type->getOffsetValueType($dimType));

				if ($result !== null) {
					return $this->issetCheck($expr->var, $typeCallback, $result);
				}
			}

			// Has offset, it is nullable
			return null;

		} elseif ($expr instanceof Node\Expr\PropertyFetch || $expr instanceof Node\Expr\StaticPropertyFetch) {

			$propertyReflection = $this->propertyReflectionFinder->findPropertyReflectionFromNode($expr, $this);

			if ($propertyReflection === null) {
				if ($expr instanceof Node\Expr\PropertyFetch) {
					return $this->issetCheckUndefined($expr->var);
				}

				if ($expr->class instanceof Expr) {
					return $this->issetCheckUndefined($expr->class);
				}

				return null;
			}

			if (!$propertyReflection->isNative()) {
				if ($expr instanceof Node\Expr\PropertyFetch) {
					return $this->issetCheckUndefined($expr->var);
				}

				if ($expr->class instanceof Expr) {
					return $this->issetCheckUndefined($expr->class);
				}

				return null;
			}

			$nativeType = $propertyReflection->getNativeType();
			if (!$nativeType instanceof MixedType) {
				if (!$this->hasExpressionType($expr)->yes()) {
					if ($expr instanceof Node\Expr\PropertyFetch) {
						return $this->issetCheckUndefined($expr->var);
					}

					if ($expr->class instanceof Expr) {
						return $this->issetCheckUndefined($expr->class);
					}

					return null;
				}
			}

			if ($result !== null) {
				return $result;
			}

			$result = $typeCallback($propertyReflection->getWritableType());
			if ($result !== null) {
				if ($expr instanceof Node\Expr\PropertyFetch) {
					return $this->issetCheck($expr->var, $typeCallback, $result);
				}

				if ($expr->class instanceof Expr) {
					return $this->issetCheck($expr->class, $typeCallback, $result);
				}
			}

			return $result;
		}

		if ($result !== null) {
			return $result;
		}

		return $typeCallback($this->getType($expr));
	}

	private function issetCheckUndefined(Expr $expr): ?bool
	{
		if ($expr instanceof Node\Expr\Variable && is_string($expr->name)) {
			$hasVariable = $this->hasVariableType($expr->name);
			if (!$hasVariable->no()) {
				return null;
			}

			return false;
		}

		if ($expr instanceof Node\Expr\ArrayDimFetch && $expr->dim !== null) {
			$type = $this->getType($expr->var);
			$dimType = $this->getType($expr->dim);
			$hasOffsetValue = $type->hasOffsetValueType($dimType);
			if (!$type->isOffsetAccessible()->yes()) {
				return $this->issetCheckUndefined($expr->var);
			}

			if (!$hasOffsetValue->no()) {
				return $this->issetCheckUndefined($expr->var);
			}

			return false;
		}

		if ($expr instanceof Expr\PropertyFetch) {
			return $this->issetCheckUndefined($expr->var);
		}

		if ($expr instanceof Expr\StaticPropertyFetch && $expr->class instanceof Expr) {
			return $this->issetCheckUndefined($expr->class);
		}

		return null;
	}

	/**
	 * @param ParametersAcceptor[] $variants
	 */
	private function createFirstClassCallable(array $variants): Type
	{
		$closureTypes = [];
		foreach ($variants as $variant) {
			$returnType = $variant->getReturnType();
			if ($variant instanceof ParametersAcceptorWithPhpDocs) {
				$returnType = $this->nativeTypesPromoted ? $variant->getNativeReturnType() : $returnType;
			}
			$parameters = $variant->getParameters();
			$closureTypes[] = new ClosureType(
				$parameters,
				$returnType,
				$variant->isVariadic(),
				$variant->getTemplateTypeMap(),
				$variant->getResolvedTemplateTypeMap(),
			);
		}

		return TypeCombinator::union(...$closureTypes);
	}

	/** @api */
	public function getNativeType(Expr $expr): Type
	{
		return $this->promoteNativeTypes()->getType($expr);
	}

	/**
	 * @api
	 * @deprecated Use getNativeType()
	 */
	public function doNotTreatPhpDocTypesAsCertain(): Scope
	{
		return $this->promoteNativeTypes();
	}

	private function promoteNativeTypes(): self
	{
		if ($this->nativeTypesPromoted) {
			return $this;
		}

		if ($this->scopeWithPromotedNativeTypes !== null) {
			return $this->scopeWithPromotedNativeTypes;
		}

		return $this->scopeWithPromotedNativeTypes = $this->scopeFactory->create(
			$this->context,
			$this->declareStrictTypes,
			$this->function,
			$this->namespace,
			$this->nativeExpressionTypes,
			[],
			[],
			$this->inClosureBindScopeClasses,
			$this->anonymousFunctionReflection,
			$this->inFirstLevelStatement,
			$this->currentlyAssignedExpressions,
			$this->currentlyAllowedUndefinedExpressions,
			$this->inFunctionCallsStack,
			$this->afterExtractCall,
			$this->parentScope,
			true,
		);
	}

	/**
	 * @param Node\Expr\PropertyFetch|Node\Expr\StaticPropertyFetch $propertyFetch
	 */
	private function hasPropertyNativeType($propertyFetch): bool
	{
		$propertyReflection = $this->propertyReflectionFinder->findPropertyReflectionFromNode($propertyFetch, $this);
		if ($propertyReflection === null) {
			return false;
		}

		if (!$propertyReflection->isNative()) {
			return false;
		}

		return !$propertyReflection->getNativeType() instanceof MixedType;
	}

	/** @api */
	protected function getTypeFromArrayDimFetch(
		Expr\ArrayDimFetch $arrayDimFetch,
		Type $offsetType,
		Type $offsetAccessibleType,
	): Type
	{
		if ($arrayDimFetch->dim === null) {
			throw new ShouldNotHappenException();
		}

		if (!$offsetAccessibleType->isArray()->yes() && (new ObjectType(ArrayAccess::class))->isSuperTypeOf($offsetAccessibleType)->yes()) {
			return $this->getType(
				new MethodCall(
					$arrayDimFetch->var,
					new Node\Identifier('offsetGet'),
					[
						new Node\Arg($arrayDimFetch->dim),
					],
				),
			);
		}

		return $offsetAccessibleType->getOffsetValueType($offsetType);
	}

	private function resolveExactName(Name $name): ?string
	{
		$originalClass = (string) $name;

		switch (strtolower($originalClass)) {
			case 'self':
				if (!$this->isInClass()) {
					return null;
				}
				return $this->getClassReflection()->getName();
			case 'parent':
				if (!$this->isInClass()) {
					return null;
				}
				$currentClassReflection = $this->getClassReflection();
				if ($currentClassReflection->getParentClass() !== null) {
					return $currentClassReflection->getParentClass()->getName();
				}
				return null;
			case 'static':
				return null;
		}

		return $originalClass;
	}

	/** @api */
	public function resolveName(Name $name): string
	{
		$originalClass = (string) $name;
		if ($this->isInClass()) {
			if (in_array(strtolower($originalClass), [
				'self',
				'static',
			], true)) {
				if ($this->inClosureBindScopeClasses !== [] && $this->inClosureBindScopeClasses !== ['static']) {
					return $this->inClosureBindScopeClasses[0];
				}
				return $this->getClassReflection()->getName();
			} elseif ($originalClass === 'parent') {
				$currentClassReflection = $this->getClassReflection();
				if ($currentClassReflection->getParentClass() !== null) {
					return $currentClassReflection->getParentClass()->getName();
				}
			}
		}

		return $originalClass;
	}

	/** @api */
	public function resolveTypeByName(Name $name): TypeWithClassName
	{
		if ($name->toLowerString() === 'static' && $this->isInClass()) {
			if ($this->inClosureBindScopeClasses !== [] && $this->inClosureBindScopeClasses !== ['static']) {
				if ($this->reflectionProvider->hasClass($this->inClosureBindScopeClasses[0])) {
					return new StaticType($this->reflectionProvider->getClass($this->inClosureBindScopeClasses[0]));
				}
			}

			return new StaticType($this->getClassReflection());
		}

		$originalClass = $this->resolveName($name);
		if ($this->isInClass()) {
			if ($this->inClosureBindScopeClasses === [$originalClass]) {
				if ($this->reflectionProvider->hasClass($originalClass)) {
					return new ThisType($this->reflectionProvider->getClass($originalClass));
				}
				return new ObjectType($originalClass);
			}

			$thisType = new ThisType($this->getClassReflection());
			$ancestor = $thisType->getAncestorWithClassName($originalClass);
			if ($ancestor !== null) {
				return $ancestor;
			}
		}

		return new ObjectType($originalClass);
	}

	/**
	 * @api
	 * @param mixed $value
	 */
	public function getTypeFromValue($value): Type
	{
		return ConstantTypeHelper::getTypeFromValue($value);
	}

	/**
	 * @api
	 * @deprecated use hasExpressionType instead
	 */
	public function isSpecified(Expr $node): bool
	{
		return !$node instanceof Variable && $this->hasExpressionType($node)->yes();
	}

	/** @api */
	public function hasExpressionType(Expr $node): TrinaryLogic
	{
		$exprString = $this->getNodeKey($node);
		if (!isset($this->expressionTypes[$exprString])) {
			return TrinaryLogic::createNo();
		}
		return $this->expressionTypes[$exprString]->getCertainty();
	}

	/**
	 * @param MethodReflection|FunctionReflection $reflection
	 */
	public function pushInFunctionCall($reflection): self
	{
		$stack = $this->inFunctionCallsStack;
		$stack[] = $reflection;

		$scope = $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$this->expressionTypes,
			$this->nativeExpressionTypes,
			$this->conditionalExpressions,
			$this->inClosureBindScopeClasses,
			$this->anonymousFunctionReflection,
			$this->isInFirstLevelStatement(),
			$this->currentlyAssignedExpressions,
			$this->currentlyAllowedUndefinedExpressions,
			$stack,
			$this->afterExtractCall,
			$this->parentScope,
			$this->nativeTypesPromoted,
		);
		$scope->resolvedTypes = $this->resolvedTypes;
		$scope->truthyScopes = $this->truthyScopes;
		$scope->falseyScopes = $this->falseyScopes;

		return $scope;
	}

	public function popInFunctionCall(): self
	{
		$stack = $this->inFunctionCallsStack;
		array_pop($stack);

		$scope = $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$this->expressionTypes,
			$this->nativeExpressionTypes,
			$this->conditionalExpressions,
			$this->inClosureBindScopeClasses,
			$this->anonymousFunctionReflection,
			$this->isInFirstLevelStatement(),
			$this->currentlyAssignedExpressions,
			$this->currentlyAllowedUndefinedExpressions,
			$stack,
			$this->afterExtractCall,
			$this->parentScope,
			$this->nativeTypesPromoted,
		);
		$scope->resolvedTypes = $this->resolvedTypes;
		$scope->truthyScopes = $this->truthyScopes;
		$scope->falseyScopes = $this->falseyScopes;

		return $scope;
	}

	/** @api */
	public function isInClassExists(string $className): bool
	{
		foreach ($this->inFunctionCallsStack as $inFunctionCall) {
			if (!$inFunctionCall instanceof FunctionReflection) {
				continue;
			}

			if (in_array($inFunctionCall->getName(), [
				'class_exists',
				'interface_exists',
				'trait_exists',
			], true)) {
				return true;
			}
		}
		$expr = new FuncCall(new FullyQualified('class_exists'), [
			new Arg(new String_(ltrim($className, '\\'))),
		]);

		return (new ConstantBooleanType(true))->isSuperTypeOf($this->getType($expr))->yes();
	}

	/** @api */
	public function isInFunctionExists(string $functionName): bool
	{
		$expr = new FuncCall(new FullyQualified('function_exists'), [
			new Arg(new String_(ltrim($functionName, '\\'))),
		]);

		return (new ConstantBooleanType(true))->isSuperTypeOf($this->getType($expr))->yes();
	}

	/** @api */
	public function enterClass(ClassReflection $classReflection): self
	{
		$thisHolder = ExpressionTypeHolder::createYes(new Variable('this'), new ThisType($classReflection));
		$constantTypes = $this->getConstantTypes();
		$constantTypes['$this'] = $thisHolder;
		$nativeConstantTypes = $this->getNativeConstantTypes();
		$nativeConstantTypes['$this'] = $thisHolder;

		return $this->scopeFactory->create(
			$this->context->enterClass($classReflection),
			$this->isDeclareStrictTypes(),
			null,
			$this->getNamespace(),
			$constantTypes,
			$nativeConstantTypes,
			[],
			[],
			null,
			true,
			[],
			[],
			[],
			false,
			$classReflection->isAnonymous() ? $this : null,
		);
	}

	public function enterTrait(ClassReflection $traitReflection): self
	{
		$namespace = null;
		$traitName = $traitReflection->getName();
		$traitNameParts = explode('\\', $traitName);
		if (count($traitNameParts) > 1) {
			$namespace = implode('\\', array_slice($traitNameParts, 0, -1));
		}
		return $this->scopeFactory->create(
			$this->context->enterTrait($traitReflection),
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$namespace,
			$this->expressionTypes,
			$this->nativeExpressionTypes,
			[],
			$this->inClosureBindScopeClasses,
			$this->anonymousFunctionReflection,
		);
	}

	/**
	 * @api
	 * @param Type[] $phpDocParameterTypes
	 * @param Type[] $parameterOutTypes
	 */
	public function enterClassMethod(
		Node\Stmt\ClassMethod $classMethod,
		TemplateTypeMap $templateTypeMap,
		array $phpDocParameterTypes,
		?Type $phpDocReturnType,
		?Type $throwType,
		?string $deprecatedDescription,
		bool $isDeprecated,
		bool $isInternal,
		bool $isFinal,
		?bool $isPure = null,
		bool $acceptsNamedArguments = true,
		?Assertions $asserts = null,
		?Type $selfOutType = null,
		?string $phpDocComment = null,
		array $parameterOutTypes = [],
	): self
	{
		if (!$this->isInClass()) {
			throw new ShouldNotHappenException();
		}

		return $this->enterFunctionLike(
			new PhpMethodFromParserNodeReflection(
				$this->getClassReflection(),
				$classMethod,
				$this->getFile(),
				$templateTypeMap,
				$this->getRealParameterTypes($classMethod),
				array_map(static fn (Type $type): Type => TemplateTypeHelper::toArgument($type), $phpDocParameterTypes),
				$this->getRealParameterDefaultValues($classMethod),
				$this->transformStaticType($this->getFunctionType($classMethod->returnType, false, false)),
				$phpDocReturnType !== null ? TemplateTypeHelper::toArgument($phpDocReturnType) : null,
				$throwType,
				$deprecatedDescription,
				$isDeprecated,
				$isInternal,
				$isFinal,
				$isPure,
				$acceptsNamedArguments,
				$asserts ?? Assertions::createEmpty(),
				$selfOutType,
				$phpDocComment,
				array_map(static fn (Type $type): Type => TemplateTypeHelper::toArgument($type), $parameterOutTypes),
			),
			!$classMethod->isStatic(),
		);
	}

	private function transformStaticType(Type $type): Type
	{
		return TypeTraverser::map($type, function (Type $type, callable $traverse): Type {
			if (!$this->isInClass()) {
				return $type;
			}
			if ($type instanceof StaticType) {
				$classReflection = $this->getClassReflection();
				$changedType = $type->changeBaseClass($classReflection);
				if ($classReflection->isFinal()) {
					$changedType = $changedType->getStaticObjectType();
				}
				return $traverse($changedType);
			}

			return $traverse($type);
		});
	}

	/**
	 * @return Type[]
	 */
	private function getRealParameterTypes(Node\FunctionLike $functionLike): array
	{
		$realParameterTypes = [];
		foreach ($functionLike->getParams() as $parameter) {
			if (!$parameter->var instanceof Variable || !is_string($parameter->var->name)) {
				throw new ShouldNotHappenException();
			}
			$realParameterTypes[$parameter->var->name] = $this->getFunctionType(
				$parameter->type,
				$this->isParameterValueNullable($parameter),
				false,
			);
		}

		return $realParameterTypes;
	}

	/**
	 * @return Type[]
	 */
	private function getRealParameterDefaultValues(Node\FunctionLike $functionLike): array
	{
		$realParameterDefaultValues = [];
		foreach ($functionLike->getParams() as $parameter) {
			if ($parameter->default === null) {
				continue;
			}
			if (!$parameter->var instanceof Variable || !is_string($parameter->var->name)) {
				throw new ShouldNotHappenException();
			}
			$realParameterDefaultValues[$parameter->var->name] = $this->getType($parameter->default);
		}

		return $realParameterDefaultValues;
	}

	/**
	 * @api
	 * @param Type[] $phpDocParameterTypes
	 * @param Type[] $parameterOutTypes
	 */
	public function enterFunction(
		Node\Stmt\Function_ $function,
		TemplateTypeMap $templateTypeMap,
		array $phpDocParameterTypes,
		?Type $phpDocReturnType,
		?Type $throwType,
		?string $deprecatedDescription,
		bool $isDeprecated,
		bool $isInternal,
		bool $isFinal,
		?bool $isPure = null,
		bool $acceptsNamedArguments = true,
		?Assertions $asserts = null,
		?string $phpDocComment = null,
		array $parameterOutTypes = [],
	): self
	{
		return $this->enterFunctionLike(
			new PhpFunctionFromParserNodeReflection(
				$function,
				$this->getFile(),
				$templateTypeMap,
				$this->getRealParameterTypes($function),
				array_map(static fn (Type $type): Type => TemplateTypeHelper::toArgument($type), $phpDocParameterTypes),
				$this->getRealParameterDefaultValues($function),
				$this->getFunctionType($function->returnType, $function->returnType === null, false),
				$phpDocReturnType !== null ? TemplateTypeHelper::toArgument($phpDocReturnType) : null,
				$throwType,
				$deprecatedDescription,
				$isDeprecated,
				$isInternal,
				$isFinal,
				$isPure,
				$acceptsNamedArguments,
				$asserts ?? Assertions::createEmpty(),
				$phpDocComment,
				array_map(static fn (Type $type): Type => TemplateTypeHelper::toArgument($type), $parameterOutTypes),
			),
			false,
		);
	}

	private function enterFunctionLike(
		PhpFunctionFromParserNodeReflection $functionReflection,
		bool $preserveThis,
	): self
	{
		$expressionTypes = [];
		$nativeExpressionTypes = [];
		foreach (ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getParameters() as $parameter) {
			$parameterType = $parameter->getType();
			$paramExprString = '$' . $parameter->getName();
			if ($parameter->isVariadic()) {
				if ($this->phpVersion->supportsNamedArguments() && $functionReflection->acceptsNamedArguments()) {
					$parameterType = new ArrayType(new UnionType([new IntegerType(), new StringType()]), $parameterType);
				} else {
					$parameterType = AccessoryArrayListType::intersectWith(new ArrayType(new IntegerType(), $parameterType));
				}
			}
			$parameterNode = new Variable($parameter->getName());
			$expressionTypes[$paramExprString] = ExpressionTypeHolder::createYes($parameterNode, $parameterType);

			$nativeParameterType = $parameter->getNativeType();
			if ($parameter->isVariadic()) {
				if ($this->phpVersion->supportsNamedArguments() && $functionReflection->acceptsNamedArguments()) {
					$nativeParameterType = new ArrayType(new UnionType([new IntegerType(), new StringType()]), $nativeParameterType);
				} else {
					$nativeParameterType = AccessoryArrayListType::intersectWith(new ArrayType(new IntegerType(), $nativeParameterType));
				}
			}
			$nativeExpressionTypes[$paramExprString] = ExpressionTypeHolder::createYes($parameterNode, $nativeParameterType);
		}

		if ($preserveThis && array_key_exists('$this', $this->expressionTypes)) {
			$expressionTypes['$this'] = $this->expressionTypes['$this'];
		}
		if ($preserveThis && array_key_exists('$this', $this->nativeExpressionTypes)) {
			$nativeExpressionTypes['$this'] = $this->nativeExpressionTypes['$this'];
		}

		return $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$functionReflection,
			$this->getNamespace(),
			array_merge($this->getConstantTypes(), $expressionTypes),
			array_merge($this->getNativeConstantTypes(), $nativeExpressionTypes),
		);
	}

	/** @api */
	public function enterNamespace(string $namespaceName): self
	{
		return $this->scopeFactory->create(
			$this->context->beginFile(),
			$this->isDeclareStrictTypes(),
			null,
			$namespaceName,
		);
	}

	/**
	 * @param list<string> $scopeClasses
	 */
	public function enterClosureBind(?Type $thisType, ?Type $nativeThisType, array $scopeClasses): self
	{
		$expressionTypes = $this->expressionTypes;
		if ($thisType !== null) {
			$expressionTypes['$this'] = ExpressionTypeHolder::createYes(new Variable('this'), $thisType);
		} else {
			unset($expressionTypes['$this']);
		}

		$nativeExpressionTypes = $this->nativeExpressionTypes;
		if ($nativeThisType !== null) {
			$nativeExpressionTypes['$this'] = ExpressionTypeHolder::createYes(new Variable('this'), $nativeThisType);
		} else {
			unset($nativeExpressionTypes['$this']);
		}

		if ($scopeClasses === ['static'] && $this->isInClass()) {
			$scopeClasses = [$this->getClassReflection()->getName()];
		}

		return $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$expressionTypes,
			$nativeExpressionTypes,
			$this->conditionalExpressions,
			$scopeClasses,
			$this->anonymousFunctionReflection,
		);
	}

	public function restoreOriginalScopeAfterClosureBind(self $originalScope): self
	{
		$expressionTypes = $this->expressionTypes;
		if (isset($originalScope->expressionTypes['$this'])) {
			$expressionTypes['$this'] = $originalScope->expressionTypes['$this'];
		} else {
			unset($expressionTypes['$this']);
		}

		$nativeExpressionTypes = $this->nativeExpressionTypes;
		if (isset($originalScope->nativeExpressionTypes['$this'])) {
			$nativeExpressionTypes['$this'] = $originalScope->nativeExpressionTypes['$this'];
		} else {
			unset($nativeExpressionTypes['$this']);
		}

		return $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$expressionTypes,
			$nativeExpressionTypes,
			$this->conditionalExpressions,
			$originalScope->inClosureBindScopeClasses,
			$this->anonymousFunctionReflection,
		);
	}

	public function enterClosureCall(Type $thisType, Type $nativeThisType): self
	{
		$expressionTypes = $this->expressionTypes;
		$expressionTypes['$this'] = ExpressionTypeHolder::createYes(new Variable('this'), $thisType);

		$nativeExpressionTypes = $this->nativeExpressionTypes;
		$nativeExpressionTypes['$this'] = ExpressionTypeHolder::createYes(new Variable('this'), $nativeThisType);

		return $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$expressionTypes,
			$nativeExpressionTypes,
			$this->conditionalExpressions,
			$thisType->getObjectClassNames(),
			$this->anonymousFunctionReflection,
		);
	}

	/** @api */
	public function isInClosureBind(): bool
	{
		return $this->inClosureBindScopeClasses !== [];
	}

	/**
	 * @api
	 * @param ParameterReflection[]|null $callableParameters
	 */
	public function enterAnonymousFunction(
		Expr\Closure $closure,
		?array $callableParameters = null,
	): self
	{
		$anonymousFunctionReflection = $this->getType($closure);
		if (!$anonymousFunctionReflection instanceof ClosureType) {
			throw new ShouldNotHappenException();
		}

		$scope = $this->enterAnonymousFunctionWithoutReflection($closure, $callableParameters);

		return $this->scopeFactory->create(
			$scope->context,
			$scope->isDeclareStrictTypes(),
			$scope->getFunction(),
			$scope->getNamespace(),
			$scope->expressionTypes,
			$scope->nativeExpressionTypes,
			[],
			$scope->inClosureBindScopeClasses,
			$anonymousFunctionReflection,
			true,
			[],
			[],
			[],
			false,
			$this,
			$this->nativeTypesPromoted,
		);
	}

	/**
	 * @param ParameterReflection[]|null $callableParameters
	 */
	private function enterAnonymousFunctionWithoutReflection(
		Expr\Closure $closure,
		?array $callableParameters = null,
	): self
	{
		$expressionTypes = [];
		$nativeTypes = [];
		foreach ($closure->params as $i => $parameter) {
			if (!$parameter->var instanceof Variable || !is_string($parameter->var->name)) {
				throw new ShouldNotHappenException();
			}
			$paramExprString = sprintf('$%s', $parameter->var->name);
			$isNullable = $this->isParameterValueNullable($parameter);
			$parameterType = $this->getFunctionType($parameter->type, $isNullable, $parameter->variadic);
			if ($callableParameters !== null) {
				if (isset($callableParameters[$i])) {
					$parameterType = TypehintHelper::decideType($parameterType, $callableParameters[$i]->getType());
				} elseif (count($callableParameters) > 0) {
					$lastParameter = $callableParameters[count($callableParameters) - 1];
					if ($lastParameter->isVariadic()) {
						$parameterType = TypehintHelper::decideType($parameterType, $lastParameter->getType());
					} else {
						$parameterType = TypehintHelper::decideType($parameterType, new MixedType());
					}
				} else {
					$parameterType = TypehintHelper::decideType($parameterType, new MixedType());
				}
			}
			$holder = ExpressionTypeHolder::createYes($parameter->var, $parameterType);
			$expressionTypes[$paramExprString] = $holder;
			$nativeTypes[$paramExprString] = $holder;
		}

		$nonRefVariableNames = [];
		foreach ($closure->uses as $use) {
			if (!is_string($use->var->name)) {
				throw new ShouldNotHappenException();
			}
			$variableName = $use->var->name;
			$paramExprString = '$' . $use->var->name;
			if ($use->byRef) {
				$holder = ExpressionTypeHolder::createYes($use->var, new MixedType());
				$expressionTypes[$paramExprString] = $holder;
				$nativeTypes[$paramExprString] = $holder;
				continue;
			}
			$nonRefVariableNames[$variableName] = true;
			if ($this->hasVariableType($variableName)->no()) {
				$variableType = new ErrorType();
				$variableNativeType = new ErrorType();
			} else {
				$variableType = $this->getVariableType($variableName);
				$variableNativeType = $this->getNativeType($use->var);
			}
			$expressionTypes[$paramExprString] = ExpressionTypeHolder::createYes($use->var, $variableType);
			$nativeTypes[$paramExprString] = ExpressionTypeHolder::createYes($use->var, $variableNativeType);
		}

		foreach ($this->invalidateStaticExpressions($this->expressionTypes) as $exprString => $typeHolder) {
			$expr = $typeHolder->getExpr();
			if ($expr instanceof Variable) {
				continue;
			}
			$variables = (new NodeFinder())->findInstanceOf([$expr], Variable::class);
			if ($variables === [] && !$this->expressionTypeIsUnchangeable($typeHolder)) {
				continue;
			}
			foreach ($variables as $variable) {
				if (!$variable instanceof Variable) {
					continue 2;
				}
				if (!is_string($variable->name)) {
					continue 2;
				}
				if (!array_key_exists($variable->name, $nonRefVariableNames)) {
					continue 2;
				}
			}

			$expressionTypes[$exprString] = $typeHolder;
		}

		if ($this->hasVariableType('this')->yes() && !$closure->static) {
			$node = new Variable('this');
			$expressionTypes['$this'] = ExpressionTypeHolder::createYes($node, $this->getType($node));
			$nativeTypes['$this'] = ExpressionTypeHolder::createYes($node, $this->getNativeType($node));
		}

		return $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			array_merge($this->getConstantTypes(), $expressionTypes),
			array_merge($this->getNativeConstantTypes(), $nativeTypes),
			[],
			$this->inClosureBindScopeClasses,
			new TrivialParametersAcceptor(),
			true,
			[],
			[],
			[],
			false,
			$this,
			$this->nativeTypesPromoted,
		);
	}

	private function expressionTypeIsUnchangeable(ExpressionTypeHolder $typeHolder): bool
	{
		$expr = $typeHolder->getExpr();
		$type = $typeHolder->getType();

		return $expr instanceof FuncCall
			&& !$expr->isFirstClassCallable()
			&& $expr->name instanceof FullyQualified
			&& $expr->name->toLowerString() === 'function_exists'
			&& isset($expr->getArgs()[0])
			&& count($this->getType($expr->getArgs()[0]->value)->getConstantStrings()) === 1
			&& (new ConstantBooleanType(true))->isSuperTypeOf($type)->yes();
	}

	/**
	 * @param array<string, ExpressionTypeHolder> $expressionTypes
	 * @return array<string, ExpressionTypeHolder>
	 */
	private function invalidateStaticExpressions(array $expressionTypes): array
	{
		$filteredExpressionTypes = [];
		$nodeFinder = new NodeFinder();
		foreach ($expressionTypes as $exprString => $expressionType) {
			$staticExpression = $nodeFinder->findFirst(
				[$expressionType->getExpr()],
				static fn ($node) => $node instanceof Expr\StaticCall || $node instanceof Expr\StaticPropertyFetch,
			);
			if ($staticExpression !== null) {
				continue;
			}
			$filteredExpressionTypes[$exprString] = $expressionType;
		}
		return $filteredExpressionTypes;
	}

	/**
	 * @api
	 * @param ParameterReflection[]|null $callableParameters
	 */
	public function enterArrowFunction(Expr\ArrowFunction $arrowFunction, ?array $callableParameters = null): self
	{
		$anonymousFunctionReflection = $this->getType($arrowFunction);
		if (!$anonymousFunctionReflection instanceof ClosureType) {
			throw new ShouldNotHappenException();
		}

		$scope = $this->enterArrowFunctionWithoutReflection($arrowFunction, $callableParameters);

		return $this->scopeFactory->create(
			$scope->context,
			$scope->isDeclareStrictTypes(),
			$scope->getFunction(),
			$scope->getNamespace(),
			$scope->expressionTypes,
			$scope->nativeExpressionTypes,
			$scope->conditionalExpressions,
			$scope->inClosureBindScopeClasses,
			$anonymousFunctionReflection,
			true,
			[],
			[],
			[],
			$scope->afterExtractCall,
			$scope->parentScope,
			$this->nativeTypesPromoted,
		);
	}

	/**
	 * @param ParameterReflection[]|null $callableParameters
	 */
	private function enterArrowFunctionWithoutReflection(Expr\ArrowFunction $arrowFunction, ?array $callableParameters): self
	{
		$arrowFunctionScope = $this;
		foreach ($arrowFunction->params as $i => $parameter) {
			if ($parameter->type === null) {
				$parameterType = new MixedType();
			} else {
				$isNullable = $this->isParameterValueNullable($parameter);
				$parameterType = $this->getFunctionType($parameter->type, $isNullable, $parameter->variadic);
			}

			if ($callableParameters !== null) {
				if (isset($callableParameters[$i])) {
					$parameterType = TypehintHelper::decideType($parameterType, $callableParameters[$i]->getType());
				} elseif (count($callableParameters) > 0) {
					$lastParameter = $callableParameters[count($callableParameters) - 1];
					if ($lastParameter->isVariadic()) {
						$parameterType = TypehintHelper::decideType($parameterType, $lastParameter->getType());
					} else {
						$parameterType = TypehintHelper::decideType($parameterType, new MixedType());
					}
				} else {
					$parameterType = TypehintHelper::decideType($parameterType, new MixedType());
				}
			}

			if (!$parameter->var instanceof Variable || !is_string($parameter->var->name)) {
				throw new ShouldNotHappenException();
			}
			$arrowFunctionScope = $arrowFunctionScope->assignVariable($parameter->var->name, $parameterType, $parameterType);
		}

		if ($arrowFunction->static) {
			$arrowFunctionScope = $arrowFunctionScope->invalidateExpression(new Variable('this'));
		}

		return $this->scopeFactory->create(
			$arrowFunctionScope->context,
			$this->isDeclareStrictTypes(),
			$arrowFunctionScope->getFunction(),
			$arrowFunctionScope->getNamespace(),
			$this->invalidateStaticExpressions($arrowFunctionScope->expressionTypes),
			$arrowFunctionScope->nativeExpressionTypes,
			$arrowFunctionScope->conditionalExpressions,
			$arrowFunctionScope->inClosureBindScopeClasses,
			null,
			true,
			[],
			[],
			[],
			$arrowFunctionScope->afterExtractCall,
			$arrowFunctionScope->parentScope,
			$this->nativeTypesPromoted,
		);
	}

	public function isParameterValueNullable(Node\Param $parameter): bool
	{
		if ($parameter->default instanceof ConstFetch) {
			return strtolower((string) $parameter->default->name) === 'null';
		}

		return false;
	}

	/**
	 * @api
	 * @param Node\Name|Node\Identifier|Node\ComplexType|null $type
	 */
	public function getFunctionType($type, bool $isNullable, bool $isVariadic): Type
	{
		if ($isNullable) {
			return TypeCombinator::addNull(
				$this->getFunctionType($type, false, $isVariadic),
			);
		}
		if ($isVariadic) {
			if ($this->phpVersion->supportsNamedArguments()) {
				return new ArrayType(new UnionType([new IntegerType(), new StringType()]), $this->getFunctionType(
					$type,
					false,
					false,
				));
			}

			return AccessoryArrayListType::intersectWith(new ArrayType(new IntegerType(), $this->getFunctionType(
				$type,
				false,
				false,
			)));
		}

		if ($type instanceof Name) {
			$className = (string) $type;
			$lowercasedClassName = strtolower($className);
			if ($lowercasedClassName === 'parent') {
				if ($this->isInClass() && $this->getClassReflection()->getParentClass() !== null) {
					return new ObjectType($this->getClassReflection()->getParentClass()->getName());
				}

				return new NonexistentParentClassType();
			}
		}

		return ParserNodeTypeToPHPStanType::resolve($type, $this->isInClass() ? $this->getClassReflection() : null);
	}

	public function enterForeach(Expr $iteratee, string $valueName, ?string $keyName): self
	{
		$iterateeType = $this->getType($iteratee);
		$nativeIterateeType = $this->getNativeType($iteratee);
		$scope = $this->assignVariable($valueName, $iterateeType->getIterableValueType(), $nativeIterateeType->getIterableValueType());
		if ($keyName !== null) {
			$scope = $scope->enterForeachKey($iteratee, $keyName);
		}

		return $scope;
	}

	public function enterForeachKey(Expr $iteratee, string $keyName): self
	{
		$iterateeType = $this->getType($iteratee);
		$nativeIterateeType = $this->getNativeType($iteratee);
		$scope = $this->assignVariable($keyName, $iterateeType->getIterableKeyType(), $nativeIterateeType->getIterableKeyType());

		if ($iterateeType->isArray()->yes()) {
			$scope = $scope->assignExpression(
				new Expr\ArrayDimFetch($iteratee, new Variable($keyName)),
				$iterateeType->getIterableValueType(),
				$nativeIterateeType->getIterableValueType(),
			);
		}

		return $scope;
	}

	/**
	 * @deprecated Use enterCatchType
	 * @param Node\Name[] $classes
	 */
	public function enterCatch(array $classes, ?string $variableName): self
	{
		$type = TypeCombinator::union(...array_map(static fn (Node\Name $class): ObjectType => new ObjectType((string) $class), $classes));

		return $this->enterCatchType($type, $variableName);
	}

	public function enterCatchType(Type $catchType, ?string $variableName): self
	{
		if ($variableName === null) {
			return $this;
		}

		return $this->assignVariable(
			$variableName,
			TypeCombinator::intersect($catchType, new ObjectType(Throwable::class)),
			TypeCombinator::intersect($catchType, new ObjectType(Throwable::class)),
		);
	}

	public function enterExpressionAssign(Expr $expr): self
	{
		$exprString = $this->getNodeKey($expr);
		$currentlyAssignedExpressions = $this->currentlyAssignedExpressions;
		$currentlyAssignedExpressions[$exprString] = true;

		$scope = $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$this->expressionTypes,
			$this->nativeExpressionTypes,
			$this->conditionalExpressions,
			$this->inClosureBindScopeClasses,
			$this->anonymousFunctionReflection,
			$this->isInFirstLevelStatement(),
			$currentlyAssignedExpressions,
			$this->currentlyAllowedUndefinedExpressions,
			[],
			$this->afterExtractCall,
			$this->parentScope,
			$this->nativeTypesPromoted,
		);
		$scope->resolvedTypes = $this->resolvedTypes;
		$scope->truthyScopes = $this->truthyScopes;
		$scope->falseyScopes = $this->falseyScopes;

		return $scope;
	}

	public function exitExpressionAssign(Expr $expr): self
	{
		$exprString = $this->getNodeKey($expr);
		$currentlyAssignedExpressions = $this->currentlyAssignedExpressions;
		unset($currentlyAssignedExpressions[$exprString]);

		$scope = $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$this->expressionTypes,
			$this->nativeExpressionTypes,
			$this->conditionalExpressions,
			$this->inClosureBindScopeClasses,
			$this->anonymousFunctionReflection,
			$this->isInFirstLevelStatement(),
			$currentlyAssignedExpressions,
			$this->currentlyAllowedUndefinedExpressions,
			[],
			$this->afterExtractCall,
			$this->parentScope,
			$this->nativeTypesPromoted,
		);
		$scope->resolvedTypes = $this->resolvedTypes;
		$scope->truthyScopes = $this->truthyScopes;
		$scope->falseyScopes = $this->falseyScopes;

		return $scope;
	}

	/** @api */
	public function isInExpressionAssign(Expr $expr): bool
	{
		$exprString = $this->getNodeKey($expr);
		return array_key_exists($exprString, $this->currentlyAssignedExpressions);
	}

	public function setAllowedUndefinedExpression(Expr $expr): self
	{
		if ($this->phpVersion->deprecatesDynamicProperties() && $expr instanceof Expr\StaticPropertyFetch) {
			return $this;
		}

		$exprString = $this->getNodeKey($expr);
		$currentlyAllowedUndefinedExpressions = $this->currentlyAllowedUndefinedExpressions;
		$currentlyAllowedUndefinedExpressions[$exprString] = true;

		$scope = $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$this->expressionTypes,
			$this->nativeExpressionTypes,
			$this->conditionalExpressions,
			$this->inClosureBindScopeClasses,
			$this->anonymousFunctionReflection,
			$this->isInFirstLevelStatement(),
			$this->currentlyAssignedExpressions,
			$currentlyAllowedUndefinedExpressions,
			[],
			$this->afterExtractCall,
			$this->parentScope,
			$this->nativeTypesPromoted,
		);
		$scope->resolvedTypes = $this->resolvedTypes;
		$scope->truthyScopes = $this->truthyScopes;
		$scope->falseyScopes = $this->falseyScopes;

		return $scope;
	}

	public function unsetAllowedUndefinedExpression(Expr $expr): self
	{
		$exprString = $this->getNodeKey($expr);
		$currentlyAllowedUndefinedExpressions = $this->currentlyAllowedUndefinedExpressions;
		unset($currentlyAllowedUndefinedExpressions[$exprString]);

		$scope = $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$this->expressionTypes,
			$this->nativeExpressionTypes,
			$this->conditionalExpressions,
			$this->inClosureBindScopeClasses,
			$this->anonymousFunctionReflection,
			$this->isInFirstLevelStatement(),
			$this->currentlyAssignedExpressions,
			$currentlyAllowedUndefinedExpressions,
			[],
			$this->afterExtractCall,
			$this->parentScope,
			$this->nativeTypesPromoted,
		);
		$scope->resolvedTypes = $this->resolvedTypes;
		$scope->truthyScopes = $this->truthyScopes;
		$scope->falseyScopes = $this->falseyScopes;

		return $scope;
	}

	/** @api */
	public function isUndefinedExpressionAllowed(Expr $expr): bool
	{
		$exprString = $this->getNodeKey($expr);
		return array_key_exists($exprString, $this->currentlyAllowedUndefinedExpressions);
	}

	public function assignVariable(string $variableName, Type $type, Type $nativeType, ?TrinaryLogic $certainty = null): self
	{
		$node = new Variable($variableName);
		$scope = $this->assignExpression($node, $type, $nativeType);
		if ($certainty !== null) {
			if ($certainty->no()) {
				throw new ShouldNotHappenException();
			} elseif (!$certainty->yes()) {
				$exprString = '$' . $variableName;
				$scope->expressionTypes[$exprString] = new ExpressionTypeHolder($node, $type, $certainty);
				$scope->nativeExpressionTypes[$exprString] = new ExpressionTypeHolder($node, $nativeType, $certainty);
			}
		}

		return $scope;
	}

	public function unsetExpression(Expr $expr): self
	{
		$scope = $this;
		if ($expr instanceof Expr\ArrayDimFetch && $expr->dim !== null) {
			$exprVarType = $scope->getType($expr->var);
			$dimType = $scope->getType($expr->dim);
			$unsetType = $exprVarType->unsetOffset($dimType);
			$exprVarNativeType = $scope->getNativeType($expr->var);
			$dimNativeType = $scope->getNativeType($expr->dim);
			$unsetNativeType = $exprVarNativeType->unsetOffset($dimNativeType);
			$scope = $scope->assignExpression($expr->var, $unsetType, $unsetNativeType)->invalidateExpression(
				new FuncCall(new FullyQualified('count'), [new Arg($expr->var)]),
			)->invalidateExpression(
				new FuncCall(new FullyQualified('sizeof'), [new Arg($expr->var)]),
			)->invalidateExpression(
				new FuncCall(new Name('count'), [new Arg($expr->var)]),
			)->invalidateExpression(
				new FuncCall(new Name('sizeof'), [new Arg($expr->var)]),
			);

			if ($expr->var instanceof Expr\ArrayDimFetch && $expr->var->dim !== null) {
				$scope = $scope->assignExpression(
					$expr->var->var,
					$this->getType($expr->var->var)->setOffsetValueType(
						$scope->getType($expr->var->dim),
						$scope->getType($expr->var),
					),
					$this->getNativeType($expr->var->var)->setOffsetValueType(
						$scope->getNativeType($expr->var->dim),
						$scope->getNativeType($expr->var),
					),
				);
			}
		}

		return $scope->invalidateExpression($expr);
	}

	public function specifyExpressionType(Expr $expr, Type $type, Type $nativeType): self
	{
		if ($expr instanceof ConstFetch) {
			$loweredConstName = strtolower($expr->name->toString());
			if (in_array($loweredConstName, ['true', 'false', 'null'], true)) {
				return $this;
			}
		}

		if ($expr instanceof FuncCall && $expr->name instanceof Name && $type->isFalse()->yes()) {
			$functionName = $this->reflectionProvider->resolveFunctionName($expr->name, $this);
			if ($functionName !== null && in_array(strtolower($functionName), [
				'is_dir',
				'is_file',
				'file_exists',
			], true)) {
				return $this;
			}
		}

		$scope = $this;
		if ($expr instanceof Expr\ArrayDimFetch && $expr->dim !== null) {
			$dimType = $scope->getType($expr->dim)->toArrayKey();
			if ($dimType instanceof ConstantIntegerType || $dimType instanceof ConstantStringType) {
				$exprVarType = $scope->getType($expr->var);
				if (!$exprVarType instanceof MixedType && !$exprVarType->isArray()->no()) {
					$types = [
						new ArrayType(new MixedType(), new MixedType()),
						new ObjectType(ArrayAccess::class),
						new NullType(),
					];
					if ($dimType instanceof ConstantIntegerType) {
						$types[] = new StringType();
					}
					$scope = $scope->specifyExpressionType(
						$expr->var,
						TypeCombinator::intersect(
							TypeCombinator::intersect($exprVarType, TypeCombinator::union(...$types)),
							new HasOffsetValueType($dimType, $type),
						),
						$scope->getNativeType($expr->var),
					);
				}
			}
		}

		$exprString = $this->getNodeKey($expr);
		$expressionTypes = $scope->expressionTypes;
		$expressionTypes[$exprString] = ExpressionTypeHolder::createYes($expr, $type);
		$nativeTypes = $scope->nativeExpressionTypes;
		$nativeTypes[$exprString] = ExpressionTypeHolder::createYes($expr, $nativeType);

		return $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$expressionTypes,
			$nativeTypes,
			$this->conditionalExpressions,
			$this->inClosureBindScopeClasses,
			$this->anonymousFunctionReflection,
			$this->inFirstLevelStatement,
			$this->currentlyAssignedExpressions,
			$this->currentlyAllowedUndefinedExpressions,
			$this->inFunctionCallsStack,
			$this->afterExtractCall,
			$this->parentScope,
			$this->nativeTypesPromoted,
		);
	}

	public function assignExpression(Expr $expr, Type $type, ?Type $nativeType = null): self
	{
		if ($nativeType === null) {
			$nativeType = new MixedType();
		}
		$scope = $this;
		if ($expr instanceof PropertyFetch) {
			$scope = $this->invalidateExpression($expr)
				->invalidateMethodsOnExpression($expr->var);
		} elseif ($expr instanceof Expr\StaticPropertyFetch) {
			$scope = $this->invalidateExpression($expr);
		} elseif ($expr instanceof Variable) {
			$scope = $this->invalidateExpression($expr);
		}

		return $scope->specifyExpressionType($expr, $type, $nativeType);
	}

	public function invalidateExpression(Expr $expressionToInvalidate, bool $requireMoreCharacters = false): self
	{
		$expressionTypes = $this->expressionTypes;
		$nativeExpressionTypes = $this->nativeExpressionTypes;
		$invalidated = false;
		$exprStringToInvalidate = $this->getNodeKey($expressionToInvalidate);

		foreach ($expressionTypes as $exprString => $exprTypeHolder) {
			$exprExpr = $exprTypeHolder->getExpr();
			if (!$this->shouldInvalidateExpression($exprStringToInvalidate, $expressionToInvalidate, $exprExpr, $requireMoreCharacters)) {
				continue;
			}

			unset($expressionTypes[$exprString]);
			unset($nativeExpressionTypes[$exprString]);
			$invalidated = true;
		}

		$newConditionalExpressions = [];
		foreach ($this->conditionalExpressions as $conditionalExprString => $holders) {
			if (count($holders) === 0) {
				continue;
			}
			if ($this->shouldInvalidateExpression($exprStringToInvalidate, $expressionToInvalidate, $holders[array_key_first($holders)]->getTypeHolder()->getExpr())) {
				$invalidated = true;
				continue;
			}
			foreach ($holders as $holder) {
				$conditionalTypeHolders = $holder->getConditionExpressionTypeHolders();
				foreach ($conditionalTypeHolders as $conditionalTypeHolder) {
					if ($this->shouldInvalidateExpression($exprStringToInvalidate, $expressionToInvalidate, $conditionalTypeHolder->getExpr())) {
						$invalidated = true;
						continue 3;
					}
				}
			}
			$newConditionalExpressions[$conditionalExprString] = $holders;
		}

		if (!$invalidated) {
			return $this;
		}

		return $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$expressionTypes,
			$nativeExpressionTypes,
			$newConditionalExpressions,
			$this->inClosureBindScopeClasses,
			$this->anonymousFunctionReflection,
			$this->inFirstLevelStatement,
			$this->currentlyAssignedExpressions,
			$this->currentlyAllowedUndefinedExpressions,
			[],
			$this->afterExtractCall,
			$this->parentScope,
			$this->nativeTypesPromoted,
		);
	}

	private function shouldInvalidateExpression(string $exprStringToInvalidate, Expr $exprToInvalidate, Expr $expr, bool $requireMoreCharacters = false): bool
	{
		if ($requireMoreCharacters && $exprStringToInvalidate === $this->getNodeKey($expr)) {
			return false;
		}

		// Variables will not contain traversable expressions. skip the NodeFinder overhead
		if ($expr instanceof Variable && is_string($expr->name)) {
			return $exprStringToInvalidate === $this->getNodeKey($expr);
		}

		$nodeFinder = new NodeFinder();
		$expressionToInvalidateClass = get_class($exprToInvalidate);
		$found = $nodeFinder->findFirst([$expr], function (Node $node) use ($expressionToInvalidateClass, $exprStringToInvalidate): bool {
			if (!$node instanceof $expressionToInvalidateClass) {
				return false;
			}

			$nodeString = $this->getNodeKey($node);

			return $nodeString === $exprStringToInvalidate;
		});

		if ($found === null) {
			return false;
		}

		if ($this->phpVersion->supportsReadOnlyProperties() && $expr instanceof PropertyFetch && $expr->name instanceof Node\Identifier && $requireMoreCharacters) {
			$propertyReflection = $this->propertyReflectionFinder->findPropertyReflectionFromNode($expr, $this);
			if ($propertyReflection !== null) {
				$nativePropertyReflection = $propertyReflection->getNativeReflection();
				if ($nativePropertyReflection !== null && $nativePropertyReflection->isReadOnly()) {
					return false;
				}
			}
		}

		return true;
	}

	private function invalidateMethodsOnExpression(Expr $expressionToInvalidate): self
	{
		$exprStringToInvalidate = $this->getNodeKey($expressionToInvalidate);
		$expressionTypes = $this->expressionTypes;
		$nativeExpressionTypes = $this->nativeExpressionTypes;
		$invalidated = false;
		$nodeFinder = new NodeFinder();
		foreach ($expressionTypes as $exprString => $exprTypeHolder) {
			$expr = $exprTypeHolder->getExpr();
			$found = $nodeFinder->findFirst([$expr], function (Node $node) use ($exprStringToInvalidate): bool {
				if (!$node instanceof MethodCall) {
					return false;
				}

				return $this->getNodeKey($node->var) === $exprStringToInvalidate;
			});
			if ($found === null) {
				continue;
			}

			unset($expressionTypes[$exprString]);
			unset($nativeExpressionTypes[$exprString]);
			$invalidated = true;
		}

		if (!$invalidated) {
			return $this;
		}

		return $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$expressionTypes,
			$nativeExpressionTypes,
			$this->conditionalExpressions,
			$this->inClosureBindScopeClasses,
			$this->anonymousFunctionReflection,
			$this->inFirstLevelStatement,
			$this->currentlyAssignedExpressions,
			$this->currentlyAllowedUndefinedExpressions,
			[],
			$this->afterExtractCall,
			$this->parentScope,
			$this->nativeTypesPromoted,
		);
	}

	private function addTypeToExpression(Expr $expr, Type $type): self
	{
		$originalExprType = $this->getType($expr);
		$nativeType = $this->getNativeType($expr);

		if ($originalExprType->equals($nativeType)) {
			$newType = TypeCombinator::intersect($type, $originalExprType);
			return $this->specifyExpressionType($expr, $newType, $newType);
		}

		return $this->specifyExpressionType(
			$expr,
			TypeCombinator::intersect($type, $originalExprType),
			TypeCombinator::intersect($type, $nativeType),
		);
	}

	public function removeTypeFromExpression(Expr $expr, Type $typeToRemove): self
	{
		$exprType = $this->getType($expr);
		if (
			$exprType instanceof NeverType ||
			$typeToRemove instanceof NeverType
		) {
			return $this;
		}
		return $this->specifyExpressionType(
			$expr,
			TypeCombinator::remove($exprType, $typeToRemove),
			TypeCombinator::remove($this->getNativeType($expr), $typeToRemove),
		);
	}

	/**
	 * @api
	 * @return MutatingScope
	 */
	public function filterByTruthyValue(Expr $expr): Scope
	{
		$exprString = $this->getNodeKey($expr);
		if (array_key_exists($exprString, $this->truthyScopes)) {
			return $this->truthyScopes[$exprString];
		}

		$specifiedTypes = $this->typeSpecifier->specifyTypesInCondition($this, $expr, TypeSpecifierContext::createTruthy());
		$scope = $this->filterBySpecifiedTypes($specifiedTypes);
		$this->truthyScopes[$exprString] = $scope;

		return $scope;
	}

	/**
	 * @api
	 * @return MutatingScope
	 */
	public function filterByFalseyValue(Expr $expr): Scope
	{
		$exprString = $this->getNodeKey($expr);
		if (array_key_exists($exprString, $this->falseyScopes)) {
			return $this->falseyScopes[$exprString];
		}

		$specifiedTypes = $this->typeSpecifier->specifyTypesInCondition($this, $expr, TypeSpecifierContext::createFalsey());
		$scope = $this->filterBySpecifiedTypes($specifiedTypes);
		$this->falseyScopes[$exprString] = $scope;

		return $scope;
	}

	public function filterBySpecifiedTypes(SpecifiedTypes $specifiedTypes): self
	{
		$typeSpecifications = [];
		foreach ($specifiedTypes->getSureTypes() as $exprString => [$expr, $type]) {
			if ($expr instanceof Node\Scalar || $expr instanceof Array_ || $expr instanceof Expr\UnaryMinus && $expr->expr instanceof Node\Scalar) {
				continue;
			}
			$typeSpecifications[] = [
				'sure' => true,
				'exprString' => $exprString,
				'expr' => $expr,
				'type' => $type,
			];
		}
		foreach ($specifiedTypes->getSureNotTypes() as $exprString => [$expr, $type]) {
			if ($expr instanceof Node\Scalar || $expr instanceof Array_ || $expr instanceof Expr\UnaryMinus && $expr->expr instanceof Node\Scalar) {
				continue;
			}
			$typeSpecifications[] = [
				'sure' => false,
				'exprString' => $exprString,
				'expr' => $expr,
				'type' => $type,
			];
		}

		usort($typeSpecifications, static function (array $a, array $b): int {
			$length = strlen($a['exprString']) - strlen($b['exprString']);
			if ($length !== 0) {
				return $length;
			}

			return $b['sure'] - $a['sure']; // @phpstan-ignore-line
		});

		$scope = $this;
		$specifiedExpressions = [];
		foreach ($typeSpecifications as $typeSpecification) {
			$expr = $typeSpecification['expr'];
			$type = $typeSpecification['type'];
			if ($typeSpecification['sure']) {
				if ($specifiedTypes->shouldOverwrite()) {
					$scope = $scope->assignExpression($expr, $type, $type);
				} else {
					$scope = $scope->addTypeToExpression($expr, $type);
				}
			} else {
				$scope = $scope->removeTypeFromExpression($expr, $type);
			}
			$specifiedExpressions[$this->getNodeKey($expr)] = ExpressionTypeHolder::createYes($expr, $scope->getType($expr));
		}

		foreach ($scope->conditionalExpressions as $conditionalExprString => $conditionalExpressions) {
			foreach ($conditionalExpressions as $conditionalExpression) {
				foreach ($conditionalExpression->getConditionExpressionTypeHolders() as $holderExprString => $conditionalTypeHolder) {
					if (!array_key_exists($holderExprString, $specifiedExpressions) || !$specifiedExpressions[$holderExprString]->equals($conditionalTypeHolder)) {
						continue 2;
					}
				}

				if ($conditionalExpression->getTypeHolder()->getCertainty()->no()) {
					unset($scope->expressionTypes[$conditionalExprString]);
				} else {
					$scope->expressionTypes[$conditionalExprString] = array_key_exists($conditionalExprString, $scope->expressionTypes)
						? new ExpressionTypeHolder(
							$scope->expressionTypes[$conditionalExprString]->getExpr(),
							TypeCombinator::intersect($scope->expressionTypes[$conditionalExprString]->getType(), $conditionalExpression->getTypeHolder()->getType()),
							TrinaryLogic::maxMin($scope->expressionTypes[$conditionalExprString]->getCertainty(), $conditionalExpression->getTypeHolder()->getCertainty()),
						)
						: $conditionalExpression->getTypeHolder();
					$specifiedExpressions[$conditionalExprString] = $conditionalExpression->getTypeHolder();
				}
			}
		}

		return $scope->scopeFactory->create(
			$scope->context,
			$scope->isDeclareStrictTypes(),
			$scope->getFunction(),
			$scope->getNamespace(),
			$scope->expressionTypes,
			$scope->nativeExpressionTypes,
			array_merge($specifiedTypes->getNewConditionalExpressionHolders(), $scope->conditionalExpressions),
			$scope->inClosureBindScopeClasses,
			$scope->anonymousFunctionReflection,
			$scope->inFirstLevelStatement,
			$scope->currentlyAssignedExpressions,
			$scope->currentlyAllowedUndefinedExpressions,
			$scope->inFunctionCallsStack,
			$scope->afterExtractCall,
			$scope->parentScope,
			$scope->nativeTypesPromoted,
		);
	}

	/**
	 * @param ConditionalExpressionHolder[] $conditionalExpressionHolders
	 */
	public function addConditionalExpressions(string $exprString, array $conditionalExpressionHolders): self
	{
		$conditionalExpressions = $this->conditionalExpressions;
		$conditionalExpressions[$exprString] = $conditionalExpressionHolders;
		return $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$this->expressionTypes,
			$this->nativeExpressionTypes,
			$conditionalExpressions,
			$this->inClosureBindScopeClasses,
			$this->anonymousFunctionReflection,
			$this->inFirstLevelStatement,
			$this->currentlyAssignedExpressions,
			$this->currentlyAllowedUndefinedExpressions,
			$this->inFunctionCallsStack,
			$this->afterExtractCall,
			$this->parentScope,
			$this->nativeTypesPromoted,
		);
	}

	public function exitFirstLevelStatements(): self
	{
		if (!$this->inFirstLevelStatement) {
			return $this;
		}

		if ($this->scopeOutOfFirstLevelStatement !== null) {
			return $this->scopeOutOfFirstLevelStatement;
		}

		$scope = $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$this->expressionTypes,
			$this->nativeExpressionTypes,
			$this->conditionalExpressions,
			$this->inClosureBindScopeClasses,
			$this->anonymousFunctionReflection,
			false,
			$this->currentlyAssignedExpressions,
			$this->currentlyAllowedUndefinedExpressions,
			$this->inFunctionCallsStack,
			$this->afterExtractCall,
			$this->parentScope,
			$this->nativeTypesPromoted,
		);
		$scope->resolvedTypes = $this->resolvedTypes;
		$scope->truthyScopes = $this->truthyScopes;
		$scope->falseyScopes = $this->falseyScopes;
		$this->scopeOutOfFirstLevelStatement = $scope;

		return $scope;
	}

	/** @api */
	public function isInFirstLevelStatement(): bool
	{
		return $this->inFirstLevelStatement;
	}

	public function mergeWith(?self $otherScope): self
	{
		if ($otherScope === null) {
			return $this;
		}
		$ourExpressionTypes = $this->expressionTypes;
		$theirExpressionTypes = $otherScope->expressionTypes;

		$mergedExpressionTypes = $this->mergeVariableHolders($ourExpressionTypes, $theirExpressionTypes);
		$conditionalExpressions = $this->intersectConditionalExpressions($otherScope->conditionalExpressions);
		$conditionalExpressions = $this->createConditionalExpressions(
			$conditionalExpressions,
			$ourExpressionTypes,
			$theirExpressionTypes,
			$mergedExpressionTypes,
		);
		$conditionalExpressions = $this->createConditionalExpressions(
			$conditionalExpressions,
			$theirExpressionTypes,
			$ourExpressionTypes,
			$mergedExpressionTypes,
		);
		return $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$mergedExpressionTypes,
			$this->mergeVariableHolders($this->nativeExpressionTypes, $otherScope->nativeExpressionTypes),
			$conditionalExpressions,
			$this->inClosureBindScopeClasses,
			$this->anonymousFunctionReflection,
			$this->inFirstLevelStatement,
			[],
			[],
			[],
			$this->afterExtractCall && $otherScope->afterExtractCall,
			$this->parentScope,
			$this->nativeTypesPromoted,
		);
	}

	/**
	 * @param array<string, ConditionalExpressionHolder[]> $otherConditionalExpressions
	 * @return array<string, ConditionalExpressionHolder[]>
	 */
	private function intersectConditionalExpressions(array $otherConditionalExpressions): array
	{
		$newConditionalExpressions = [];
		foreach ($this->conditionalExpressions as $exprString => $holders) {
			if (!array_key_exists($exprString, $otherConditionalExpressions)) {
				continue;
			}

			$otherHolders = $otherConditionalExpressions[$exprString];
			foreach (array_keys($holders) as $key) {
				if (!array_key_exists($key, $otherHolders)) {
					continue 2;
				}
			}

			$newConditionalExpressions[$exprString] = $holders;
		}

		return $newConditionalExpressions;
	}

	/**
	 * @param array<string, ConditionalExpressionHolder[]> $conditionalExpressions
	 * @param array<string, ExpressionTypeHolder> $ourExpressionTypes
	 * @param array<string, ExpressionTypeHolder> $theirExpressionTypes
	 * @param array<string, ExpressionTypeHolder> $mergedExpressionTypes
	 * @return array<string, ConditionalExpressionHolder[]>
	 */
	private function createConditionalExpressions(
		array $conditionalExpressions,
		array $ourExpressionTypes,
		array $theirExpressionTypes,
		array $mergedExpressionTypes,
	): array
	{
		$newVariableTypes = $ourExpressionTypes;
		foreach ($theirExpressionTypes as $exprString => $holder) {
			if (!array_key_exists($exprString, $mergedExpressionTypes)) {
				continue;
			}

			if (!$mergedExpressionTypes[$exprString]->getType()->equals($holder->getType())) {
				continue;
			}

			unset($newVariableTypes[$exprString]);
		}

		$typeGuards = [];
		foreach ($newVariableTypes as $exprString => $holder) {
			if (!$holder->getCertainty()->yes()) {
				continue;
			}
			if (!array_key_exists($exprString, $mergedExpressionTypes)) {
				continue;
			}
			if ($mergedExpressionTypes[$exprString]->getType()->equals($holder->getType())) {
				continue;
			}

			$typeGuards[$exprString] = $holder;
		}

		if (count($typeGuards) === 0) {
			return $conditionalExpressions;
		}

		foreach ($newVariableTypes as $exprString => $holder) {
			if (
				array_key_exists($exprString, $mergedExpressionTypes)
				&& $mergedExpressionTypes[$exprString]->equals($holder)
			) {
				continue;
			}

			$variableTypeGuards = $typeGuards;
			unset($variableTypeGuards[$exprString]);

			if (count($variableTypeGuards) === 0) {
				continue;
			}

			$conditionalExpression = new ConditionalExpressionHolder($variableTypeGuards, $holder);
			$conditionalExpressions[$exprString][$conditionalExpression->getKey()] = $conditionalExpression;
		}

		foreach ($mergedExpressionTypes as $exprString => $mergedExprTypeHolder) {
			if (array_key_exists($exprString, $ourExpressionTypes)) {
				continue;
			}

			$conditionalExpression = new ConditionalExpressionHolder($typeGuards, new ExpressionTypeHolder($mergedExprTypeHolder->getExpr(), new ErrorType(), TrinaryLogic::createNo()));
			$conditionalExpressions[$exprString][$conditionalExpression->getKey()] = $conditionalExpression;
		}

		return $conditionalExpressions;
	}

	/**
	 * @param array<string, ExpressionTypeHolder> $ourVariableTypeHolders
	 * @param array<string, ExpressionTypeHolder> $theirVariableTypeHolders
	 * @return array<string, ExpressionTypeHolder>
	 */
	private function mergeVariableHolders(array $ourVariableTypeHolders, array $theirVariableTypeHolders): array
	{
		$intersectedVariableTypeHolders = [];
		foreach ($ourVariableTypeHolders as $exprString => $variableTypeHolder) {
			if (isset($theirVariableTypeHolders[$exprString])) {
				$intersectedVariableTypeHolders[$exprString] = $variableTypeHolder->and($theirVariableTypeHolders[$exprString]);
			} else {
				$intersectedVariableTypeHolders[$exprString] = ExpressionTypeHolder::createMaybe($variableTypeHolder->getExpr(), $variableTypeHolder->getType());
			}
		}

		foreach ($theirVariableTypeHolders as $exprString => $variableTypeHolder) {
			if (isset($intersectedVariableTypeHolders[$exprString])) {
				continue;
			}

			$intersectedVariableTypeHolders[$exprString] = ExpressionTypeHolder::createMaybe($variableTypeHolder->getExpr(), $variableTypeHolder->getType());
		}

		return $intersectedVariableTypeHolders;
	}

	public function processFinallyScope(self $finallyScope, self $originalFinallyScope): self
	{
		return $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$this->processFinallyScopeVariableTypeHolders(
				$this->expressionTypes,
				$finallyScope->expressionTypes,
				$originalFinallyScope->expressionTypes,
			),
			$this->processFinallyScopeVariableTypeHolders(
				$this->nativeExpressionTypes,
				$finallyScope->nativeExpressionTypes,
				$originalFinallyScope->nativeExpressionTypes,
			),
			$this->conditionalExpressions,
			$this->inClosureBindScopeClasses,
			$this->anonymousFunctionReflection,
			$this->inFirstLevelStatement,
			[],
			[],
			[],
			$this->afterExtractCall,
			$this->parentScope,
			$this->nativeTypesPromoted,
		);
	}

	/**
	 * @param array<string, ExpressionTypeHolder> $ourVariableTypeHolders
	 * @param array<string, ExpressionTypeHolder> $finallyVariableTypeHolders
	 * @param array<string, ExpressionTypeHolder> $originalVariableTypeHolders
	 * @return array<string, ExpressionTypeHolder>
	 */
	private function processFinallyScopeVariableTypeHolders(
		array $ourVariableTypeHolders,
		array $finallyVariableTypeHolders,
		array $originalVariableTypeHolders,
	): array
	{
		foreach ($finallyVariableTypeHolders as $exprString => $variableTypeHolder) {
			if (
				isset($originalVariableTypeHolders[$exprString])
				&& !$originalVariableTypeHolders[$exprString]->getType()->equals($variableTypeHolder->getType())
			) {
				$ourVariableTypeHolders[$exprString] = $variableTypeHolder;
				continue;
			}

			if (isset($originalVariableTypeHolders[$exprString])) {
				continue;
			}

			$ourVariableTypeHolders[$exprString] = $variableTypeHolder;
		}

		return $ourVariableTypeHolders;
	}

	/**
	 * @param Expr\ClosureUse[] $byRefUses
	 */
	public function processClosureScope(
		self $closureScope,
		?self $prevScope,
		array $byRefUses,
	): self
	{
		$nativeExpressionTypes = $this->nativeExpressionTypes;
		$expressionTypes = $this->expressionTypes;
		if (count($byRefUses) === 0) {
			return $this;
		}

		foreach ($byRefUses as $use) {
			if (!is_string($use->var->name)) {
				throw new ShouldNotHappenException();
			}

			$variableName = $use->var->name;
			$variableExprString = '$' . $variableName;

			if (!$closureScope->hasVariableType($variableName)->yes()) {
				$holder = ExpressionTypeHolder::createYes($use->var, new NullType());
				$expressionTypes[$variableExprString] = $holder;
				$nativeExpressionTypes[$variableExprString] = $holder;
				continue;
			}

			$variableType = $closureScope->getVariableType($variableName);

			if ($prevScope !== null) {
				$prevVariableType = $prevScope->getVariableType($variableName);
				if (!$variableType->equals($prevVariableType)) {
					$variableType = TypeCombinator::union($variableType, $prevVariableType);
					$variableType = self::generalizeType($variableType, $prevVariableType, 0);
				}
			}

			$expressionTypes[$variableExprString] = ExpressionTypeHolder::createYes($use->var, $variableType);
			$nativeExpressionTypes[$variableExprString] = ExpressionTypeHolder::createYes($use->var, $variableType);
		}

		return $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$expressionTypes,
			$nativeExpressionTypes,
			$this->conditionalExpressions,
			$this->inClosureBindScopeClasses,
			$this->anonymousFunctionReflection,
			$this->inFirstLevelStatement,
			[],
			[],
			$this->inFunctionCallsStack,
			$this->afterExtractCall,
			$this->parentScope,
			$this->nativeTypesPromoted,
		);
	}

	public function processAlwaysIterableForeachScopeWithoutPollute(self $finalScope): self
	{
		$expressionTypes = $this->expressionTypes;
		foreach ($finalScope->expressionTypes as $variableExprString => $variableTypeHolder) {
			if (!isset($expressionTypes[$variableExprString])) {
				$expressionTypes[$variableExprString] = ExpressionTypeHolder::createMaybe($variableTypeHolder->getExpr(), $variableTypeHolder->getType());
				continue;
			}

			$expressionTypes[$variableExprString] = new ExpressionTypeHolder(
				$variableTypeHolder->getExpr(),
				$variableTypeHolder->getType(),
				$variableTypeHolder->getCertainty()->and($expressionTypes[$variableExprString]->getCertainty()),
			);
		}
		$nativeTypes = $this->nativeExpressionTypes;
		foreach ($finalScope->nativeExpressionTypes as $variableExprString => $variableTypeHolder) {
			if (!isset($nativeTypes[$variableExprString])) {
				$nativeTypes[$variableExprString] = ExpressionTypeHolder::createMaybe($variableTypeHolder->getExpr(), $variableTypeHolder->getType());
				continue;
			}

			$nativeTypes[$variableExprString] = new ExpressionTypeHolder(
				$variableTypeHolder->getExpr(),
				$variableTypeHolder->getType(),
				$variableTypeHolder->getCertainty()->and($nativeTypes[$variableExprString]->getCertainty()),
			);
		}

		return $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$expressionTypes,
			$nativeTypes,
			$this->conditionalExpressions,
			$this->inClosureBindScopeClasses,
			$this->anonymousFunctionReflection,
			$this->inFirstLevelStatement,
			[],
			[],
			[],
			$this->afterExtractCall,
			$this->parentScope,
			$this->nativeTypesPromoted,
		);
	}

	public function generalizeWith(self $otherScope): self
	{
		$variableTypeHolders = $this->generalizeVariableTypeHolders(
			$this->expressionTypes,
			$otherScope->expressionTypes,
		);
		$nativeTypes = $this->generalizeVariableTypeHolders(
			$this->nativeExpressionTypes,
			$otherScope->nativeExpressionTypes,
		);

		return $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$variableTypeHolders,
			$nativeTypes,
			$this->conditionalExpressions,
			$this->inClosureBindScopeClasses,
			$this->anonymousFunctionReflection,
			$this->inFirstLevelStatement,
			[],
			[],
			[],
			$this->afterExtractCall,
			$this->parentScope,
			$this->nativeTypesPromoted,
		);
	}

	/**
	 * @param array<string, ExpressionTypeHolder> $variableTypeHolders
	 * @param array<string, ExpressionTypeHolder> $otherVariableTypeHolders
	 * @return array<string, ExpressionTypeHolder>
	 */
	private function generalizeVariableTypeHolders(
		array $variableTypeHolders,
		array $otherVariableTypeHolders,
	): array
	{
		foreach ($variableTypeHolders as $variableExprString => $variableTypeHolder) {
			if (!isset($otherVariableTypeHolders[$variableExprString])) {
				continue;
			}

			$variableTypeHolders[$variableExprString] = new ExpressionTypeHolder(
				$variableTypeHolder->getExpr(),
				self::generalizeType($variableTypeHolder->getType(), $otherVariableTypeHolders[$variableExprString]->getType(), 0),
				$variableTypeHolder->getCertainty(),
			);
		}

		return $variableTypeHolders;
	}

	private static function generalizeType(Type $a, Type $b, int $depth): Type
	{
		if ($a->equals($b)) {
			return $a;
		}

		$constantIntegers = ['a' => [], 'b' => []];
		$constantFloats = ['a' => [], 'b' => []];
		$constantBooleans = ['a' => [], 'b' => []];
		$constantStrings = ['a' => [], 'b' => []];
		$constantArrays = ['a' => [], 'b' => []];
		$generalArrays = ['a' => [], 'b' => []];
		$integerRanges = ['a' => [], 'b' => []];
		$otherTypes = [];

		foreach ([
			'a' => TypeUtils::flattenTypes($a),
			'b' => TypeUtils::flattenTypes($b),
		] as $key => $types) {
			foreach ($types as $type) {
				if ($type instanceof ConstantIntegerType) {
					$constantIntegers[$key][] = $type;
					continue;
				}
				if ($type instanceof ConstantFloatType) {
					$constantFloats[$key][] = $type;
					continue;
				}
				if ($type instanceof ConstantBooleanType) {
					$constantBooleans[$key][] = $type;
					continue;
				}
				if ($type instanceof ConstantStringType) {
					$constantStrings[$key][] = $type;
					continue;
				}
				if ($type->isConstantArray()->yes()) {
					$constantArrays[$key][] = $type;
					continue;
				}
				if ($type->isArray()->yes()) {
					$generalArrays[$key][] = $type;
					continue;
				}
				if ($type instanceof IntegerRangeType) {
					$integerRanges[$key][] = $type;
					continue;
				}

				$otherTypes[] = $type;
			}
		}

		$resultTypes = [];
		foreach ([
			$constantFloats,
			$constantBooleans,
			$constantStrings,
		] as $constantTypes) {
			if (count($constantTypes['a']) === 0) {
				if (count($constantTypes['b']) > 0) {
					$resultTypes[] = TypeCombinator::union(...$constantTypes['b']);
				}
				continue;
			} elseif (count($constantTypes['b']) === 0) {
				$resultTypes[] = TypeCombinator::union(...$constantTypes['a']);
				continue;
			}

			$aTypes = TypeCombinator::union(...$constantTypes['a']);
			$bTypes = TypeCombinator::union(...$constantTypes['b']);
			if ($aTypes->equals($bTypes)) {
				$resultTypes[] = $aTypes;
				continue;
			}

			$resultTypes[] = TypeCombinator::union(...$constantTypes['a'], ...$constantTypes['b'])->generalize(GeneralizePrecision::moreSpecific());
		}

		if (count($constantArrays['a']) > 0) {
			if (count($constantArrays['b']) === 0) {
				$resultTypes[] = TypeCombinator::union(...$constantArrays['a']);
			} else {
				$constantArraysA = TypeCombinator::union(...$constantArrays['a']);
				$constantArraysB = TypeCombinator::union(...$constantArrays['b']);
				if ($constantArraysA->getIterableKeyType()->equals($constantArraysB->getIterableKeyType())) {
					$resultArrayBuilder = ConstantArrayTypeBuilder::createEmpty();
					foreach (TypeUtils::flattenTypes($constantArraysA->getIterableKeyType()) as $keyType) {
						$resultArrayBuilder->setOffsetValueType(
							$keyType,
							self::generalizeType(
								$constantArraysA->getOffsetValueType($keyType),
								$constantArraysB->getOffsetValueType($keyType),
								$depth + 1,
							),
							!$constantArraysA->hasOffsetValueType($keyType)->and($constantArraysB->hasOffsetValueType($keyType))->negate()->no(),
						);
					}

					$resultTypes[] = $resultArrayBuilder->getArray();
				} else {
					$resultType = new ArrayType(
						TypeCombinator::union(self::generalizeType($constantArraysA->getIterableKeyType(), $constantArraysB->getIterableKeyType(), $depth + 1)),
						TypeCombinator::union(self::generalizeType($constantArraysA->getIterableValueType(), $constantArraysB->getIterableValueType(), $depth + 1)),
					);
					if ($constantArraysA->isIterableAtLeastOnce()->yes() && $constantArraysB->isIterableAtLeastOnce()->yes()) {
						$resultType = TypeCombinator::intersect($resultType, new NonEmptyArrayType());
					}
					if ($constantArraysA->isList()->yes() && $constantArraysB->isList()->yes()) {
						$resultType = AccessoryArrayListType::intersectWith($resultType);
					}
					$resultTypes[] = $resultType;
				}
			}
		} elseif (count($constantArrays['b']) > 0) {
			$resultTypes[] = TypeCombinator::union(...$constantArrays['b']);
		}

		if (count($generalArrays['a']) > 0) {
			if (count($generalArrays['b']) === 0) {
				$resultTypes[] = TypeCombinator::union(...$generalArrays['a']);
			} else {
				$generalArraysA = TypeCombinator::union(...$generalArrays['a']);
				$generalArraysB = TypeCombinator::union(...$generalArrays['b']);

				$aValueType = $generalArraysA->getIterableValueType();
				$bValueType = $generalArraysB->getIterableValueType();
				if (
					$aValueType->isArray()->yes()
					&& $aValueType->isConstantArray()->no()
					&& $bValueType->isArray()->yes()
					&& $bValueType->isConstantArray()->no()
				) {
					$aDepth = self::getArrayDepth($aValueType) + $depth;
					$bDepth = self::getArrayDepth($bValueType) + $depth;
					if (
						($aDepth > 2 || $bDepth > 2)
						&& abs($aDepth - $bDepth) > 0
					) {
						$aValueType = new MixedType();
						$bValueType = new MixedType();
					}
				}

				$resultType = new ArrayType(
					TypeCombinator::union(self::generalizeType($generalArraysA->getIterableKeyType(), $generalArraysB->getIterableKeyType(), $depth + 1)),
					TypeCombinator::union(self::generalizeType($aValueType, $bValueType, $depth + 1)),
				);
				if ($generalArraysA->isIterableAtLeastOnce()->yes() && $generalArraysB->isIterableAtLeastOnce()->yes()) {
					$resultType = TypeCombinator::intersect($resultType, new NonEmptyArrayType());
				}
				if ($generalArraysA->isList()->yes() && $generalArraysB->isList()->yes()) {
					$resultType = AccessoryArrayListType::intersectWith($resultType);
				}
				if ($generalArraysA->isOversizedArray()->yes() && $generalArraysB->isOversizedArray()->yes()) {
					$resultType = TypeCombinator::intersect($resultType, new OversizedArrayType());
				}
				$resultTypes[] = $resultType;
			}
		} elseif (count($generalArrays['b']) > 0) {
			$resultTypes[] = TypeCombinator::union(...$generalArrays['b']);
		}

		if (count($constantIntegers['a']) > 0) {
			if (count($constantIntegers['b']) === 0) {
				$resultTypes[] = TypeCombinator::union(...$constantIntegers['a']);
			} else {
				$constantIntegersA = TypeCombinator::union(...$constantIntegers['a']);
				$constantIntegersB = TypeCombinator::union(...$constantIntegers['b']);

				if ($constantIntegersA->equals($constantIntegersB)) {
					$resultTypes[] = $constantIntegersA;
				} else {
					$min = null;
					$max = null;
					foreach ($constantIntegers['a'] as $int) {
						if ($min === null || $int->getValue() < $min) {
							$min = $int->getValue();
						}
						if ($max !== null && $int->getValue() <= $max) {
							continue;
						}

						$max = $int->getValue();
					}

					$gotGreater = false;
					$gotSmaller = false;
					foreach ($constantIntegers['b'] as $int) {
						if ($int->getValue() > $max) {
							$gotGreater = true;
						}
						if ($int->getValue() >= $min) {
							continue;
						}

						$gotSmaller = true;
					}

					if ($gotGreater && $gotSmaller) {
						$resultTypes[] = new IntegerType();
					} elseif ($gotGreater) {
						$resultTypes[] = IntegerRangeType::fromInterval($min, null);
					} elseif ($gotSmaller) {
						$resultTypes[] = IntegerRangeType::fromInterval(null, $max);
					} else {
						$resultTypes[] = TypeCombinator::union($constantIntegersA, $constantIntegersB);
					}
				}
			}
		} elseif (count($constantIntegers['b']) > 0) {
			$resultTypes[] = TypeCombinator::union(...$constantIntegers['b']);
		}

		if (count($integerRanges['a']) > 0) {
			if (count($integerRanges['b']) === 0) {
				$resultTypes[] = TypeCombinator::union(...$integerRanges['a']);
			} else {
				$integerRangesA = TypeCombinator::union(...$integerRanges['a']);
				$integerRangesB = TypeCombinator::union(...$integerRanges['b']);

				if ($integerRangesA->equals($integerRangesB)) {
					$resultTypes[] = $integerRangesA;
				} else {
					$min = null;
					$max = null;
					foreach ($integerRanges['a'] as $range) {
						if ($range->getMin() === null) {
							$rangeMin = PHP_INT_MIN;
						} else {
							$rangeMin = $range->getMin();
						}
						if ($range->getMax() === null) {
							$rangeMax = PHP_INT_MAX;
						} else {
							$rangeMax = $range->getMax();
						}

						if ($min === null || $rangeMin < $min) {
							$min = $rangeMin;
						}
						if ($max !== null && $rangeMax <= $max) {
							continue;
						}

						$max = $rangeMax;
					}

					$gotGreater = false;
					$gotSmaller = false;
					foreach ($integerRanges['b'] as $range) {
						if ($range->getMin() === null) {
							$rangeMin = PHP_INT_MIN;
						} else {
							$rangeMin = $range->getMin();
						}
						if ($range->getMax() === null) {
							$rangeMax = PHP_INT_MAX;
						} else {
							$rangeMax = $range->getMax();
						}

						if ($rangeMax > $max) {
							$gotGreater = true;
						}
						if ($rangeMin >= $min) {
							continue;
						}

						$gotSmaller = true;
					}

					if ($min === PHP_INT_MIN) {
						$min = null;
					}
					if ($max === PHP_INT_MAX) {
						$max = null;
					}

					if ($gotGreater && $gotSmaller) {
						$resultTypes[] = new IntegerType();
					} elseif ($gotGreater) {
						$resultTypes[] = IntegerRangeType::fromInterval($min, null);
					} elseif ($gotSmaller) {
						$resultTypes[] = IntegerRangeType::fromInterval(null, $max);
					} else {
						$resultTypes[] = TypeCombinator::union($integerRangesA, $integerRangesB);
					}
				}
			}
		} elseif (count($integerRanges['b']) > 0) {
			$resultTypes[] = TypeCombinator::union(...$integerRanges['b']);
		}

		$accessoryTypes = array_map(
			static fn (Type $type): Type => $type->generalize(GeneralizePrecision::moreSpecific()),
			TypeUtils::getAccessoryTypes($a),
		);

		return TypeCombinator::intersect(
			TypeCombinator::union(...$resultTypes, ...$otherTypes),
			...$accessoryTypes,
		);
	}

	private static function getArrayDepth(Type $type): int
	{
		$depth = 0;
		$arrays = TypeUtils::getAnyArrays($type);
		while (count($arrays) > 0) {
			$temp = $type->getIterableValueType();
			$type = $temp;
			$arrays = TypeUtils::getAnyArrays($type);
			$depth++;
		}

		return $depth;
	}

	public function equals(self $otherScope): bool
	{
		if (!$this->context->equals($otherScope->context)) {
			return false;
		}

		if (!$this->compareVariableTypeHolders($this->expressionTypes, $otherScope->expressionTypes)) {
			return false;
		}
		return $this->compareVariableTypeHolders($this->nativeExpressionTypes, $otherScope->nativeExpressionTypes);
	}

	/**
	 * @param array<string, ExpressionTypeHolder> $variableTypeHolders
	 * @param array<string, ExpressionTypeHolder> $otherVariableTypeHolders
	 */
	private function compareVariableTypeHolders(array $variableTypeHolders, array $otherVariableTypeHolders): bool
	{
		if (count($variableTypeHolders) !== count($otherVariableTypeHolders)) {
			return false;
		}
		foreach ($variableTypeHolders as $variableExprString => $variableTypeHolder) {
			if (!isset($otherVariableTypeHolders[$variableExprString])) {
				return false;
			}

			if (!$variableTypeHolder->getCertainty()->equals($otherVariableTypeHolders[$variableExprString]->getCertainty())) {
				return false;
			}

			if (!$variableTypeHolder->getType()->equals($otherVariableTypeHolders[$variableExprString]->getType())) {
				return false;
			}

			unset($otherVariableTypeHolders[$variableExprString]);
		}

		return true;
	}

	/** @api */
	public function canAccessProperty(PropertyReflection $propertyReflection): bool
	{
		return $this->canAccessClassMember($propertyReflection);
	}

	/** @api */
	public function canCallMethod(MethodReflection $methodReflection): bool
	{
		if ($this->canAccessClassMember($methodReflection)) {
			return true;
		}

		return $this->canAccessClassMember($methodReflection->getPrototype());
	}

	/** @api */
	public function canAccessConstant(ConstantReflection $constantReflection): bool
	{
		return $this->canAccessClassMember($constantReflection);
	}

	private function canAccessClassMember(ClassMemberReflection $classMemberReflection): bool
	{
		if ($classMemberReflection->isPublic()) {
			return true;
		}

		$classReflectionName = $classMemberReflection->getDeclaringClass()->getName();
		$canAccessClassMember = static function (ClassReflection $classReflection) use ($classMemberReflection, $classReflectionName) {
			if ($classMemberReflection->isPrivate()) {
				return $classReflection->getName() === $classReflectionName;
			}

			// protected

			if (
				$classReflection->getName() === $classReflectionName
				|| $classReflection->isSubclassOf($classReflectionName)
			) {
				return true;
			}

			return $classMemberReflection->getDeclaringClass()->isSubclassOf($classReflection->getName());
		};

		foreach ($this->inClosureBindScopeClasses as $inClosureBindScopeClass) {
			if (!$this->reflectionProvider->hasClass($inClosureBindScopeClass)) {
				continue;
			}

			if ($canAccessClassMember($this->reflectionProvider->getClass($inClosureBindScopeClass))) {
				return true;
			}
		}

		if ($this->isInClass()) {
			return $canAccessClassMember($this->getClassReflection());
		}

		return false;
	}

	/**
	 * @return string[]
	 */
	public function debug(): array
	{
		$descriptions = [];
		foreach ($this->expressionTypes as $name => $variableTypeHolder) {
			$key = sprintf('%s (%s)', $name, $variableTypeHolder->getCertainty()->describe());
			$descriptions[$key] = $variableTypeHolder->getType()->describe(VerbosityLevel::precise());
		}
		foreach ($this->nativeExpressionTypes as $exprString => $nativeTypeHolder) {
			$key = sprintf('native %s', $exprString);
			$descriptions[$key] = $nativeTypeHolder->getType()->describe(VerbosityLevel::precise());
		}

		return $descriptions;
	}

	private function exactInstantiation(New_ $node, string $className): ?Type
	{
		$resolvedClassName = $this->resolveExactName(new Name($className));
		if ($resolvedClassName === null) {
			return null;
		}

		if (!$this->reflectionProvider->hasClass($resolvedClassName)) {
			return null;
		}

		$classReflection = $this->reflectionProvider->getClass($resolvedClassName);
		if ($classReflection->hasConstructor()) {
			$constructorMethod = $classReflection->getConstructor();
		} else {
			$constructorMethod = new DummyConstructorReflection($classReflection);
		}

		$resolvedTypes = [];
		$methodCall = new Expr\StaticCall(
			new Name($resolvedClassName),
			new Node\Identifier($constructorMethod->getName()),
			$node->getArgs(),
		);

		$parametersAcceptor = ParametersAcceptorSelector::selectFromArgs(
			$this,
			$methodCall->getArgs(),
			$constructorMethod->getVariants(),
		);
		$normalizedMethodCall = ArgumentsNormalizer::reorderStaticCallArguments($parametersAcceptor, $methodCall);

		if ($normalizedMethodCall !== null) {
			foreach ($this->dynamicReturnTypeExtensionRegistry->getDynamicStaticMethodReturnTypeExtensionsForClass($classReflection->getName()) as $dynamicStaticMethodReturnTypeExtension) {
				if (!$dynamicStaticMethodReturnTypeExtension->isStaticMethodSupported($constructorMethod)) {
					continue;
				}

				$resolvedType = $dynamicStaticMethodReturnTypeExtension->getTypeFromStaticMethodCall(
					$constructorMethod,
					$normalizedMethodCall,
					$this,
				);
				if ($resolvedType === null) {
					continue;
				}

				$resolvedTypes[] = $resolvedType;
			}
		}

		if (count($resolvedTypes) > 0) {
			return TypeCombinator::union(...$resolvedTypes);
		}

		$methodResult = $this->getType($methodCall);
		if ($methodResult instanceof NeverType && $methodResult->isExplicit()) {
			return $methodResult;
		}

		$objectType = new ObjectType($resolvedClassName);
		if (!$classReflection->isGeneric()) {
			return $objectType;
		}

		$assignedToProperty = $node->getAttribute(NewAssignedToPropertyVisitor::ATTRIBUTE_NAME);
		if ($assignedToProperty !== null) {
			$constructorVariant = ParametersAcceptorSelector::selectSingle($constructorMethod->getVariants());
			$classTemplateTypes = $classReflection->getTemplateTypeMap()->getTypes();
			$originalClassTemplateTypes = $classTemplateTypes;
			foreach ($constructorVariant->getParameters() as $parameter) {
				TypeTraverser::map($parameter->getType(), static function (Type $type, callable $traverse) use (&$classTemplateTypes): Type {
					if ($type instanceof TemplateType && array_key_exists($type->getName(), $classTemplateTypes)) {
						$classTemplateType = $classTemplateTypes[$type->getName()];
						if ($classTemplateType instanceof TemplateType && $classTemplateType->getScope()->equals($type->getScope())) {
							unset($classTemplateTypes[$type->getName()]);
						}
						return $type;
					}

					return $traverse($type);
				});
			}

			if (count($classTemplateTypes) === count($originalClassTemplateTypes)) {
				$propertyType = TypeCombinator::removeNull($this->getType($assignedToProperty));
				if ($objectType->isSuperTypeOf($propertyType)->yes()) {
					return $propertyType;
				}
			}
		}

		if ($constructorMethod instanceof DummyConstructorReflection || $constructorMethod->getDeclaringClass()->getName() !== $classReflection->getName()) {
			return new GenericObjectType(
				$resolvedClassName,
				$classReflection->typeMapToList($classReflection->getTemplateTypeMap()->resolveToBounds()),
			);
		}

		$parametersAcceptor = ParametersAcceptorSelector::selectFromArgs(
			$this,
			$methodCall->getArgs(),
			$constructorMethod->getVariants(),
		);

		if ($this->explicitMixedInUnknownGenericNew) {
			return new GenericObjectType(
				$resolvedClassName,
				$classReflection->typeMapToList($parametersAcceptor->getResolvedTemplateTypeMap()),
			);
		}

		$resolvedPhpDoc = $classReflection->getResolvedPhpDoc();
		if ($resolvedPhpDoc === null) {
			return $objectType;
		}

		$list = [];
		$typeMap = $parametersAcceptor->getResolvedTemplateTypeMap();
		foreach ($resolvedPhpDoc->getTemplateTags() as $tag) {
			$templateType = $typeMap->getType($tag->getName());
			if ($templateType !== null) {
				$list[] = $templateType;
				continue;
			}
			$bound = $tag->getBound();
			if ($bound instanceof MixedType && $bound->isExplicitMixed()) {
				$bound = new MixedType(false);
			}
			$list[] = $bound;
		}

		return new GenericObjectType(
			$resolvedClassName,
			$list,
		);
	}

	private function filterTypeWithMethod(Type $typeWithMethod, string $methodName): ?Type
	{
		if ($typeWithMethod instanceof UnionType) {
			$newTypes = [];
			foreach ($typeWithMethod->getTypes() as $innerType) {
				if (!$innerType->hasMethod($methodName)->yes()) {
					continue;
				}

				$newTypes[] = $innerType;
			}
			if (count($newTypes) === 0) {
				return null;
			}
			$typeWithMethod = TypeCombinator::union(...$newTypes);
		}

		if (!$typeWithMethod->hasMethod($methodName)->yes()) {
			return null;
		}

		return $typeWithMethod;
	}

	/** @api */
	public function getMethodReflection(Type $typeWithMethod, string $methodName): ?ExtendedMethodReflection
	{
		$type = $this->filterTypeWithMethod($typeWithMethod, $methodName);
		if ($type === null) {
			return null;
		}

		return $type->getMethod($methodName, $this);
	}

	/**
	 * @param MethodCall|Node\Expr\StaticCall $methodCall
	 */
	private function methodCallReturnType(Type $typeWithMethod, string $methodName, Expr $methodCall): ?Type
	{
		$typeWithMethod = $this->filterTypeWithMethod($typeWithMethod, $methodName);
		if ($typeWithMethod === null) {
			return null;
		}

		$methodReflection = $typeWithMethod->getMethod($methodName, $this);
		$parametersAcceptor = ParametersAcceptorSelector::selectFromArgs(
			$this,
			$methodCall->getArgs(),
			$methodReflection->getVariants(),
		);
		if ($methodCall instanceof MethodCall) {
			$normalizedMethodCall = ArgumentsNormalizer::reorderMethodArguments($parametersAcceptor, $methodCall);
		} else {
			$normalizedMethodCall = ArgumentsNormalizer::reorderStaticCallArguments($parametersAcceptor, $methodCall);
		}
		if ($normalizedMethodCall === null) {
			return $parametersAcceptor->getReturnType();
		}

		$resolvedTypes = [];
		foreach ($typeWithMethod->getObjectClassNames() as $className) {
			if ($normalizedMethodCall instanceof MethodCall) {
				foreach ($this->dynamicReturnTypeExtensionRegistry->getDynamicMethodReturnTypeExtensionsForClass($className) as $dynamicMethodReturnTypeExtension) {
					if (!$dynamicMethodReturnTypeExtension->isMethodSupported($methodReflection)) {
						continue;
					}

					$resolvedType = $dynamicMethodReturnTypeExtension->getTypeFromMethodCall($methodReflection, $normalizedMethodCall, $this);
					if ($resolvedType === null) {
						continue;
					}

					$resolvedTypes[] = $resolvedType;
				}
			} else {
				foreach ($this->dynamicReturnTypeExtensionRegistry->getDynamicStaticMethodReturnTypeExtensionsForClass($className) as $dynamicStaticMethodReturnTypeExtension) {
					if (!$dynamicStaticMethodReturnTypeExtension->isStaticMethodSupported($methodReflection)) {
						continue;
					}

					$resolvedType = $dynamicStaticMethodReturnTypeExtension->getTypeFromStaticMethodCall(
						$methodReflection,
						$normalizedMethodCall,
						$this,
					);
					if ($resolvedType === null) {
						continue;
					}

					$resolvedTypes[] = $resolvedType;
				}
			}
		}

		if (count($resolvedTypes) > 0) {
			return TypeCombinator::union(...$resolvedTypes);
		}

		return $parametersAcceptor->getReturnType();
	}

	/** @api */
	public function getPropertyReflection(Type $typeWithProperty, string $propertyName): ?PropertyReflection
	{
		if ($typeWithProperty instanceof UnionType) {
			$newTypes = [];
			foreach ($typeWithProperty->getTypes() as $innerType) {
				if (!$innerType->hasProperty($propertyName)->yes()) {
					continue;
				}

				$newTypes[] = $innerType;
			}
			if (count($newTypes) === 0) {
				return null;
			}
			$typeWithProperty = TypeCombinator::union(...$newTypes);
		}
		if (!$typeWithProperty->hasProperty($propertyName)->yes()) {
			return null;
		}

		return $typeWithProperty->getProperty($propertyName, $this);
	}

	/**
	 * @param PropertyFetch|Node\Expr\StaticPropertyFetch $propertyFetch
	 */
	private function propertyFetchType(Type $fetchedOnType, string $propertyName, Expr $propertyFetch): ?Type
	{
		$propertyReflection = $this->getPropertyReflection($fetchedOnType, $propertyName);
		if ($propertyReflection === null) {
			return null;
		}

		if ($this->isInExpressionAssign($propertyFetch)) {
			return $propertyReflection->getWritableType();
		}

		return $propertyReflection->getReadableType();
	}

	public function getConstantReflection(Type $typeWithConstant, string $constantName): ?ConstantReflection
	{
		if ($typeWithConstant instanceof UnionType) {
			$newTypes = [];
			foreach ($typeWithConstant->getTypes() as $innerType) {
				if (!$innerType->hasConstant($constantName)->yes()) {
					continue;
				}

				$newTypes[] = $innerType;
			}
			if (count($newTypes) === 0) {
				return null;
			}
			$typeWithConstant = TypeCombinator::union(...$newTypes);
		}
		if (!$typeWithConstant->hasConstant($constantName)->yes()) {
			return null;
		}

		return $typeWithConstant->getConstant($constantName);
	}

	/**
	 * @return array<string, ExpressionTypeHolder>
	 */
	private function getConstantTypes(): array
	{
		$constantTypes = [];
		foreach ($this->expressionTypes as $exprString => $typeHolder) {
			$expr = $typeHolder->getExpr();
			if (!$expr instanceof ConstFetch) {
				continue;
			}
			$constantTypes[$exprString] = $typeHolder;
		}
		return $constantTypes;
	}

	/**
	 * @return array<string, ExpressionTypeHolder>
	 */
	private function getNativeConstantTypes(): array
	{
		$constantTypes = [];
		foreach ($this->nativeExpressionTypes as $exprString => $typeHolder) {
			$expr = $typeHolder->getExpr();
			if (!$expr instanceof ConstFetch) {
				continue;
			}
			$constantTypes[$exprString] = $typeHolder;
		}
		return $constantTypes;
	}

}
