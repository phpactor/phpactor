<?php

namespace Phpactor\Tests\Unit\Extension\ClassMover\Command\Logger;

use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\TextDocumentBuilder;
use Symfony\Component\Console\Output\BufferedOutput;
use Phpactor\Extension\ClassMover\Command\Logger\SymfonyConsoleMoveLogger;
use Phpactor\Filesystem\Domain\FilePath;
use Phpactor\ClassMover\FoundReferences;
use Phpactor\ClassMover\Domain\Reference\NamespaceReference;
use Phpactor\ClassMover\Domain\Reference\NamespacedClassReferences;
use Phpactor\ClassMover\Domain\Reference\ClassReference;
use Phpactor\ClassMover\Domain\Name\QualifiedName;
use Phpactor\ClassMover\Domain\Name\FullyQualifiedName;
use Phpactor\ClassMover\Domain\Reference\ImportedNameReference;
use Phpactor\ClassMover\Domain\Name\Namespace_;
use Phpactor\ClassMover\Domain\Reference\Position;

class SymfonyConsoleMoveLoggerTest extends TestCase
{
    private BufferedOutput $output;

    private SymfonyConsoleMoveLogger $logger;

    public function setUp(): void
    {
        $this->output = new BufferedOutput();
        $this->logger = new SymfonyConsoleMoveLogger($this->output);
    }

    public function testReplacing(): void
    {
        $references = new FoundReferences(
            TextDocumentBuilder::create(
                <<<'EOT'
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
            )->build(),
            FullyQualifiedName::fromString('Acme'),
            NamespacedClassReferences::fromNamespaceAndClassRefs(
                NamespaceReference::fromNameAndPosition(Namespace_::fromString('Foobar'), Position::fromStartAndEnd(10, 20)),
                [
                    ClassReference::fromNameAndPosition(
                        QualifiedName::fromString('Hello'),
                        FullyQualifiedName::fromString('Foobar\Hello'),
                        Position::fromStartAndEnd(18, 20),
                        ImportedNameReference::none(),
                        false
                    )
                ]
            )
        );

        $target = FullyQualifiedName::fromString('Hello\World');
        $this->logger->replacing(FilePath::fromString('/path/to/file/Something.php'), $references, $target);
        $output = $this->output->fetch();
        $this->assertStringContainsString('Hello => World', $output);
    }
}
