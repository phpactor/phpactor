<?php

namespace Phpactor\Completion\Tests\Integration;

use Phpactor\Completion\Bridge\WorseReflection\Formatter\ClassFormatter;
use Phpactor\Completion\Bridge\WorseReflection\Formatter\ConstantFormatter;
use Phpactor\Completion\Bridge\WorseReflection\Formatter\EnumCaseFormatter;
use Phpactor\Completion\Bridge\WorseReflection\Formatter\FunctionFormatter;
use Phpactor\Completion\Bridge\WorseReflection\Formatter\InterfaceFormatter;
use Phpactor\Completion\Bridge\WorseReflection\Formatter\MethodFormatter;
use Phpactor\Completion\Bridge\WorseReflection\Formatter\ParameterFormatter;
use Phpactor\Completion\Bridge\WorseReflection\Formatter\ParametersFormatter;
use Phpactor\Completion\Bridge\WorseReflection\Formatter\PropertyFormatter;
use Phpactor\Completion\Bridge\WorseReflection\Formatter\TraitFormatter;
use Phpactor\Completion\Bridge\WorseReflection\Formatter\TypeFormatter;
use Phpactor\Completion\Bridge\WorseReflection\Formatter\VariableFormatter;
use Phpactor\Completion\Bridge\WorseReflection\SnippetFormatter\FunctionLikeSnippetFormatter;
use Phpactor\Completion\Bridge\WorseReflection\SnippetFormatter\NameSearchResultClassSnippetFormatter;
use Phpactor\Completion\Bridge\WorseReflection\SnippetFormatter\NameSearchResultFunctionSnippetFormatter;
use Phpactor\Completion\Bridge\WorseReflection\SnippetFormatter\ParametersSnippetFormatter;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\Completion\Tests\TestCase;
use Phpactor\WorseReflection\Reflector;

class IntegrationTestCase extends TestCase
{
    protected function formatter(): ObjectFormatter
    {
        return new ObjectFormatter([
            new TypeFormatter(),
            new FunctionFormatter(),
            new MethodFormatter(),
            new ParameterFormatter(),
            new ParametersFormatter(),
            new PropertyFormatter(),
            new VariableFormatter(),
            new InterfaceFormatter(),
            new ClassFormatter(),
            new TraitFormatter(),
            new ConstantFormatter(),
            new EnumCaseFormatter(),
        ]);
    }

    protected function snippetFormatter(Reflector $reflector): ObjectFormatter
    {
        return new ObjectFormatter([
            new ParametersSnippetFormatter(),
            new FunctionLikeSnippetFormatter(),
            new NameSearchResultClassSnippetFormatter($reflector),
            new NameSearchResultFunctionSnippetFormatter($reflector),
        ]);
    }
}
