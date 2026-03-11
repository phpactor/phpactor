<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\AstProvider;

use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider;
use Phpactor\WorseReflection\Core\AstProvider\CachedAstProvider;
use Phpactor\WorseReflection\Core\CacheForDocument;

final class CachedAstProviderTest extends TestCase
{
    public function testCache(): void
    {
        $provider = $this->createProvider();
        $document = TextDocumentBuilder::create('<?php echo 1;')->uri('file:///foobar')->build();

        $ast1 = $provider->get($document);
        $ast2 = $provider->get($document);
        self::assertSame($ast1, $ast2, 'Cache is used');

        $document = TextDocumentBuilder::create('<?php echo 2;')->uri('file:///foobar')->build();
        $ast2 = $provider->get($document);
        self::assertNotSame($ast1, $ast2, 'Cache is not used if document is changed');
    }

    public function testCacheAnonymousDocument(): void
    {
        $provider = $this->createProvider();
        $document = TextDocumentBuilder::create('<?php echo 1;')->build();

        $ast1 = $provider->get($document);
        $ast2 = $provider->get($document);
        self::assertSame($ast1, $ast2, 'Cache is used');

        $document = TextDocumentBuilder::create('<?php echo 2;')->build();
        $ast2 = $provider->get($document);
        self::assertNotSame($ast1, $ast2, 'Cache is not used if document is changed');
    }

    private function createProvider(): CachedAstProvider
    {
        return new CachedAstProvider(
            new TolerantAstProvider(),
            CacheForDocument::static(),
        );
    }
}
