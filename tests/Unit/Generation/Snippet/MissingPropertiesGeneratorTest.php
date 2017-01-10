<?php

namespace Phpactor\Tests\Unit\Generation\Snippet;

use Phpactor\Generation\Snippet\MissingPropertiesGenerator;
use Phpactor\CodeContext;
use BetterReflection\Reflector\ClassReflector;
use Phpactor\Util\ClassUtil;
use BetterReflection\Reflection\ReflectionClass;
use PhpParser\Node\Stmt\Class_;
use BetterReflection\Reflection\ReflectionProperty;
use Phpactor\AstVisitor\AssignedPropertiesVisitor;
use Prophecy\Argument;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Expr\New_;

class MissingPropertiesGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var mixed
     */
    private $reflector;
    
    /**
     * @var mixed
     */
    private $classUtil;
    
    /**
     * @var MissingMethodsGenerator
     */
    private $generator;
    
    /**
     * @var mixed
     */
    private $classReflection;

    /**
     * @var mixed
     */
    private $assignedPropertiesVisitor;
    
    /**
     * @var mixed
     */
    private $property;

    public function setUp()
    {
        $this->reflector = $this->prophesize(ClassReflector::class);
        $this->classUtil = $this->prophesize(ClassUtil::class);
        $this->assignedPropertiesVisitor = $this->prophesize(AssignedPropertiesVisitor::class);

        $this->generator = new MissingPropertiesGenerator(
            $this->reflector->reveal(),
            $this->classUtil->reveal(),
            $this->assignedPropertiesVisitor->reveal()
        );

        $this->classReflection = $this->prophesize(ReflectionClass::class);
        $this->classReflection->isInterface()->willReturn(false);
        $this->classReflection->getAst()->willReturn(new Class_('foobar'));
        $this->property = $this->prophesize(ReflectionProperty::class);

        $this->assignedPropertiesVisitor->beforeTraverse(Argument::any())->shouldBeCalled();
        $this->assignedPropertiesVisitor->enterNode(Argument::any())->shouldBeCalled();
        $this->assignedPropertiesVisitor->leaveNode(Argument::any())->shouldBeCalled();
        $this->assignedPropertiesVisitor->afterTraverse(Argument::any())->shouldBeCalled();
    }

    /**
     * It should add genereate a snippet for any missing assigned properties.
     */
    public function testMissingProperties()
    {
        $this->classUtil->getClassNameFromSource('somesource')->willReturn('FooClass');
        $this->reflector->reflect('FooClass')->willReturn(
            $this->classReflection->reveal()
        );
        $this->classReflection->getProperties()->willReturn([
            $this->property->reveal()
        ]);
        $this->assignedPropertiesVisitor->getAssignedPropertyNodes()->willReturn([
            new Assign(new Variable('foobar'), new Variable('barfoo')),
            new Assign(new Variable('barfoo'), new String_('barfoo')),
            new Assign(new Variable('integer'), new LNumber('foobar')),
            new Assign(new Variable('decimal'), new DNumber('barfoo')),
            new Assign(new Variable('new'), new New_('Foobar')),
        ]);

        $missing = $this->generator->generate(CodeContext::create('foofile', 'somesource', 0), []);

        $this->assertEquals(<<<'EOT'
/**
 * @var mixed
 */
private $foobar;

/**
 * @var string
 */
private $barfoo;

/**
 * @var int
 */
private $integer;

/**
 * @var float
 */
private $decimal;

/**
 * @var Foobar
 */
private $new;

EOT
        , $missing);
    }
}
