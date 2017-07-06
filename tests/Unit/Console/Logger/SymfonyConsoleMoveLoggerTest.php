<?php

namespace Phpactor\Tests\Unit\Console\Logger;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Phpactor\UserInterface\Console\Logger\SymfonyConsoleMoveLogger;
use Phpactor\ClassMover\Domain\SourceCode;
use Phpactor\ClassMover\Domain\FullyQualifiedName;
use Phpactor\ClassMover\Domain\NamespacedClassRefList;
use Phpactor\ClassMover\Domain\NamespaceRef;
use Phpactor\ClassMover\Domain\Position;
use Phpactor\ClassMover\Domain\QualifiedName;
use Phpactor\ClassMover\Domain\ImportedNameRef;
use Phpactor\ClassMover\Domain\FoundReferences;
use Phpactor\ClassMover\Domain\SourceNamespace;
use Phpactor\ClassMover\Domain\ClassRef;
use Phpactor\Filesystem\Domain\FilePath;

class SymfonyConsoleMoveLoggerTest extends TestCase
{
    private $output;
    private $logger;

    public function setUp()
    {
        $this->output = new BufferedOutput();
        $this->logger = new SymfonyConsoleMoveLogger($this->output);
    }

    public function testReplacing()
    {
        $references = new FoundReferences(
            SourceCode::fromString(<<<'EOT'
<?php

namespace Acme;

class Foobar
{
    public function source(): SourceCode
    {
        return $this->source;
    }

    public function targetName(): FullyQualifiedName
    {
        return $this->name;
    }

    public function references(): NamespacedClassRefList
    {
        return $this->references;
    }
}
EOT
            ), FullyQualifiedName::fromString('Acme'),
            NamespacedClassRefList::fromNamespaceAndClassRefs(
                NamespaceRef::fromNameAndPosition(SourceNamespace::fromString('Foobar'), Position::fromStartAndEnd(10, 20)),
                [
                    ClassRef::fromNameAndPosition(
                        QualifiedName::fromString('Hello'),
                        FullyQualifiedName::fromString('Foobar\Hello'),
                        Position::fromStartAndEnd(18, 20),
                        ImportedNameRef::none(),
                        false
                    )
                ]
            )
        );

        $target = FullyQualifiedName::fromString('Hello\World');
        $this->logger->replacing(FilePath::fromString('/path/to/file/Something.php'), $references, $target);
        $output = $this->output->fetch();
    }
}
