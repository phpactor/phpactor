<?php

namespace Phpactor\WorseReferenceFinder\Tests\Unit;

use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReferenceFinder\Tests\IntegrationTestCase;
use Phpactor\WorseReferenceFinder\WorseReflectionTypeLocator;

class WorseReflectionTypeLocatorTest extends IntegrationTestCase
{
    public function testLocatesType(): void
    {
        $location = $this->locate(
            <<<'EOT'
                // File: One.php
                // <?php class One {}
                // File: Two.php
                // <?php class Two {}
                EOT
        ,
            <<<'EOT'
                <?php

                class Foo
                {
                    /** 
                     * @var One
                     */
                    private $one;

                    public function bar()
                    {
                        $this->o<>ne;
                    }
                }
                EOT
        );
        self::assertEquals($this->workspace->path('One.php'), $location->uri()->path());
        self::assertEquals(9, $location->offset()->toInt());
    }

    public function testLocatesArrayType(): void
    {
        $location = $this->locate(
            <<<'EOT'
                // File: One.php
                // <?php class One {}
                // File: Two.php
                // <?php class Two {}
                EOT
        ,
            <<<'EOT'
                <?php

                class Foo
                {
                    /** 
                     * @var One[]
                     */
                    private $one;

                    public function bar()
                    {
                        $this->o<>ne;
                    }
                }
                EOT
        );
        self::assertEquals($this->workspace->path('One.php'), $location->uri()->path());
        self::assertEquals(9, $location->offset()->toInt());
    }

    public function testLocatesInterface(): void
    {
        $location = $this->locate(
            <<<'EOT'
                // File: One.php
                // <?php interface One {}
                // File: Two.php
                // <?php class Two {}
                EOT
        ,
            <<<'EOT'
                <?php

                class Foo
                {
                    /** 
                     * @var One
                     */
                    private $one;

                    public function bar()
                    {
                        $this->o<>ne;
                    }
                }
                EOT
        );
        self::assertEquals($this->workspace->path('One.php'), $location->uri()->path());
        self::assertEquals(9, $location->offset()->toInt());
    }

    protected function locate(string $manifset, string $source): Location
    {
        [$source, $offset] = ExtractOffset::fromSource($source);

        $this->workspace->loadManifest($manifset);

        return (new WorseReflectionTypeLocator($this->reflector()))->locateType(
            TextDocumentBuilder::create($source)->language('php')->build(),
            ByteOffset::fromInt($offset)
        );
    }
}
