<?php

namespace Phpactor\Tests\Unit\ContextAction\Action;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Reflector;
use Phpactor\ContextAction\Action\GotoDefinitionAction;
use Phpactor\WorseReflection\Core\Reflection\Inference\Symbol;
use Phpactor\WorseReflection\Core\Reflection\Inference\SymbolInformation;
use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\ContextAction\Result\GotoDefinitionResult;
use Phpactor\WorseReflection\Core\Reflection\ReflectionConstant;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionConstantCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;

class GotoDefinitionActionTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $reflector;
    /**
     * @var GotoDefinitionAction
     */
    private $action;

    /**
     * @var ObjectProphecy
     */
    private $reflectionClass;

    /**
     * @var ObjectProphecy
     */
    private $reflectionMethodCollection;

    /**
     * @var ObjectProphecy
     */
    private $reflectionMethod;

    public function setUp()
    {
        $this->reflector = $this->prophesize(Reflector::class);

        $this->action = new GotoDefinitionAction($this->reflector->reveal());

        $this->reflectionClass = $this->prophesize(ReflectionClass::class);

        $this->reflectionMethod = $this->prophesize(ReflectionMethod::class);
        $this->reflectionMethodCollection = $this->prophesize(ReflectionMethodCollection::class);

        $this->reflectionConstant = $this->prophesize(ReflectionConstant::class);
        $this->reflectionConstantCollection = $this->prophesize(ReflectionConstantCollection::class);

        $this->reflectionProperty = $this->prophesize(ReflectionProperty::class);
        $this->reflectionPropertyCollection = $this->prophesize(ReflectionPropertyCollection::class);
    }

    /**
     * It fails if it doesn't know how to resolve an action.
     */
    public function testUnresolvableSymbol()
    {
        $info = SymbolInformation::for(Symbol::unknown());
        $result = $this->action->perform($info);
        $this->assertContains('Do not know how to goto definition of symbol', (string) $result);
    }

    /**
     * Method: It fails if the containing class cannot be determined.
     */
    public function testNoContainingClass()
    {
        $info = SymbolInformation::for(
            Symbol::fromTypeNameAndPosition(Symbol::METHOD, 'aaa', Position::fromStartAndEnd(1, 2))
        );
        $result = $this->action->perform($info);
        $this->assertContains('Containing class for member "%s" could not be determined', (string) $result);
    }

    /**
     * Method: It fails if the contianing class is not found.
     */
    public function testContainingClassNotFound()
    {
        $info = SymbolInformation::for(
            Symbol::fromTypeNameAndPosition(Symbol::METHOD, 'aaa', Position::fromStartAndEnd(1, 2))
        );
        $info = $info->withClassType(Type::fromString('Foobar'));
        $this->reflector->reflectClassLike(ClassName::fromString('Foobar'))->willThrow(new NotFound('Notfound'));

        $result = $this->action->perform($info);
        $this->assertContains('Notfound', (string) $result);
    }

    /**
     * Method: It fails if the class has no path associated with it.
     */
    public function testClassNoPath()
    {
        $info = SymbolInformation::for(
            Symbol::fromTypeNameAndPosition(Symbol::METHOD, 'aaa', Position::fromStartAndEnd(1, 2))
        );
        $info = $info->withClassType(Type::fromString('Foobar'));
        $this->reflector->reflectClassLike(ClassName::fromString('Foobar'))->willReturn($this->reflectionClass->reveal());
        $this->reflectionClass->sourceCode()->willReturn(SourceCode::fromString('asd'));
        $this->reflectionClass->name()->willReturn(ClassName::fromString('asd'));

        $result = $this->action->perform($info);
        $this->assertContains('The source code for class "asd" has no path associated with it', (string) $result);
    }

    /**
     * Method: It fails if the containing class does not have the method.
     */
    public function testMethodNotFound()
    {
        $info = SymbolInformation::for(
            Symbol::fromTypeNameAndPosition(Symbol::METHOD, 'aaa', Position::fromStartAndEnd(1, 2))
        );
        $info = $info->withClassType(Type::fromString('Foobar'));
        $this->reflector->reflectClassLike(ClassName::fromString('Foobar'))->willReturn($this->reflectionClass->reveal());
        $this->reflectionClass->name()->willReturn(ClassName::fromString('class1'));
        $this->reflectionClass->methods()->willReturn($this->reflectionMethodCollection->reveal());
        $this->reflectionClass->sourceCode()->willReturn(SourceCode::fromPath(__FILE__));
        $this->reflectionMethodCollection->has('aaa')->willReturn(false);
        $this->reflectionMethodCollection->keys()->willReturn(['a', 'b', 'c']);

        $result = $this->action->perform($info);
        $this->assertContains('Class "class1" has no method named "aaa", has: "a", "b", "c"', (string) $result);
    }

    /**
     * Method: It returns the gotodefinition result.
     */
    public function testGotoDefinition()
    {
        $this->reflectionClass->methods()->willReturn($this->reflectionMethodCollection->reveal());
        $this->reflectionMethodCollection->has('aaa')->willReturn(true);
        $this->reflectionMethodCollection->get('aaa')->willReturn($this->reflectionMethod->reveal());
        $this->reflectionMethod->position()->willReturn(Position::fromStartAndEnd(10, 20));

        $this->assertGotoDefinition(Symbol::METHOD);
    }

    /**
     * Contstant: It returns the gotodefinition result.
     */
    public function testGotoDefinitionConstnat()
    {
        $this->reflectionClass->constants()->willReturn($this->reflectionConstantCollection->reveal());
        $this->reflectionConstantCollection->has('aaa')->willReturn(true);
        $this->reflectionConstantCollection->get('aaa')->willReturn($this->reflectionConstant->reveal());
        $this->reflectionConstant->position()->willReturn(Position::fromStartAndEnd(10, 20));

        $this->assertGotoDefinition(Symbol::CONSTANT);
    }

    /**
     * Property: It returns the gotodefinition result.
     */
    public function testGotoDefinitionProperty()
    {
        $this->reflectionClass->properties()->willReturn($this->reflectionPropertyCollection->reveal());
        $this->reflectionPropertyCollection->has('aaa')->willReturn(true);
        $this->reflectionPropertyCollection->get('aaa')->willReturn($this->reflectionProperty->reveal());
        $this->reflectionProperty->position()->willReturn(Position::fromStartAndEnd(10, 20));
        $this->reflectionClass->isInterface()->willReturn(false);

        $this->assertGotoDefinition(Symbol::PROPERTY);
    }

    /**
     * Property: Fails if the class is an interface.
     */
    public function testGotoDefinitionPropertyIsInterface()
    {

        $info = SymbolInformation::for(
            Symbol::fromTypeNameAndPosition(Symbol::PROPERTY, 'aaa', Position::fromStartAndEnd(1, 2))
        );
        $info = $info->withClassType(Type::fromString('Foobar'));
        $this->reflector->reflectClassLike(ClassName::fromString('Foobar'))->willReturn($this->reflectionClass->reveal());
        $this->reflectionClass->isInterface()->willReturn(true);
        $this->reflectionClass->name()->willReturn(ClassName::fromString('class1'));

        $result = $this->action->perform($info);
        $this->assertContains('Symbol is a property and class "class1" is an interface', (string) $result);
    }

    private function assertGotoDefinition($symbolType)
    {
        $info = SymbolInformation::for(
            Symbol::fromTypeNameAndPosition($symbolType, 'aaa', Position::fromStartAndEnd(1, 2))
        );
        $info = $info->withClassType(Type::fromString('Foobar'));
        $this->reflector->reflectClassLike(ClassName::fromString('Foobar'))->willReturn($this->reflectionClass->reveal());
        $this->reflectionClass->name()->willReturn(ClassName::fromString('class1'));
        $this->reflectionClass->sourceCode()->willReturn(SourceCode::fromPath(__FILE__));

        $result = $this->action->perform($info);

        $this->assertEquals(
            GotoDefinitionResult::fromClassPathAndOffset(__FILE__, 10),
            $result
        );
    }
}
