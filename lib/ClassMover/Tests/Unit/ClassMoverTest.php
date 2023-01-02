<?php

namespace Phpactor\ClassMover\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\ClassMover\Domain\ClassFinder;
use Phpactor\ClassMover\Domain\ClassReplacer;
use Phpactor\ClassMover\ClassMover;
use Phpactor\ClassMover\Domain\Reference\NamespacedClassReferences;
use Phpactor\ClassMover\FoundReferences;
use Phpactor\ClassMover\Domain\Name\FullyQualifiedName;
use Phpactor\TextDocument\TextDocumentBuilder;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ClassMoverTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<ClassFinder>
     */
    private ObjectProphecy $finder;
    /**
     * @var ObjectProphecy<ClassReplacer>
     */
    private ObjectProphecy $replacer;
    private ClassMover $mover;

    public function setUp(): void
    {
        $this->finder = $this->prophesize(ClassFinder::class);
        $this->replacer = $this->prophesize(ClassReplacer::class);

        $this->mover = new ClassMover(
            $this->finder->reveal(),
            $this->replacer->reveal()
        );
    }

    /**
     * It should delgate to the finder to find references.
     */
    public function testFindReferences(): FoundReferences
    {
        $source = TextDocumentBuilder::create('<?php echo "hello";')->build();
        $fullName = 'Something';
        $refList = NamespacedClassReferences::empty();

        $this->finder->findIn($source)->willReturn($refList);


        $references = $this->mover->findReferences($source, $fullName);

        $this->assertInstanceOf(FoundReferences::class, $references);

        $this->assertEquals($source, (string) $references->source());
        $this->assertEquals($fullName, (string) $references->targetName());
        $this->assertEquals([], iterator_to_array($references->references()));

        return $references;
    }

    /**
     * It should replace references.
     *
     * @depends testFindReferences
     */
    public function testReplaceReferences(FoundReferences $references): void
    {
        $newFqn = 'SomethingElse';

        $this->replacer->replaceReferences(
            $references->source(),
            $references->references(),
            $references->targetName(),
            FullyQualifiedName::fromString($newFqn)
        )->shouldBeCalled();

        $this->mover->replaceReferences($references, $newFqn);
    }
}
