<?php

namespace Phpactor\Tests\Unit\Generation\Snippet;

use Phpactor\Composer\ClassNameResolver;
use Phpactor\CodeContext;
use Phpactor\Generation\Snippet\ClassGenerator;
use Phpactor\Composer\ClassFqn;

class ClassGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClassNameResolver
     */
    private $resolver;

    /**
     * @var ClassGenerator
     */
    private $generator;

    public function setUp()
    {
        $this->resolver = $this->prophesize(ClassNameResolver::class);
        $this->generator = new ClassGenerator($this->resolver->reveal());
    }

    public function testGenerate()
    {
        $filename = 'foo/foobar.php';
        $this->resolver->resolve($filename)->willReturn(ClassFqn::fromString('Foo\\Bar'));

        $snippet = $this->generator->generate(CodeContext::create($filename, '', 0), []);
        $this->assertEquals(<<<EOT
<?php

namespace Foo;

class Bar
{
}
EOT
        , $snippet);
    }
}
