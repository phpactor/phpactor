<?php

namespace Phpactor\CodeTransform\Tests\Adapter\DocblockParser;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeTransform\Adapter\DocblockParser\ParserDocblockUpdater;
use Phpactor\DocblockParser\DocblockParser;
use Phpactor\WorseReflection\Core\TypeFactory;

class ParserDocblockUpdaterTest extends TestCase
{
    public function testUpdateReturnType(): void
    {
        self::assertEquals('/** @return string */', $this->createUpdater()->setReturnType(
            '/** @return Foobar */',
            TypeFactory::string()
        ));
    }

    public function testUpdateReturnTypeWithMultipleTags(): void
    {
        self::assertEquals(<<<'EOT'
                /** 
                 * This is some text
                 * @param Foobar
                 * @return string 
                 * @return string 
                 */
                EOT, 
                $this->createUpdater()->setReturnType(<<<'EOT'
                /** 
                 * This is some text
                 * @param Foobar
                 * @return Bazboo 
                 * @return Foobar 
                 */
                EOT,
                TypeFactory::string()
        ));
    }

    public function testAddIfNotExisting(): void
    {
        self::assertEquals('/** @return string */', $this->createUpdater()->setReturnType(
            '/** */',
            TypeFactory::string()
        ));
    }

    public function testAddIfNotExistingMultiline(): void
    {
        self::assertEquals(<<<'EOT'
                /** 
                 *
                 * @return string
                 */
            EOT, 
                $this->createUpdater()->setReturnType(<<<'EOT'
                /** 
                 *
                 */
            EOT,
                TypeFactory::string()
        ));
    }


    private function createUpdater(): ParserDocblockUpdater
    {
        return (new ParserDocblockUpdater(DocblockParser::create()));
    }
}
