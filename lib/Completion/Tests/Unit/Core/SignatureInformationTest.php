<?php

namespace Phpactor\Completion\Tests\Unit\Core;

use Phpactor\Completion\Core\SignatureInformation;
use PHPUnit\Framework\TestCase;

class SignatureInformationTest extends TestCase
{
    public function testSignatureWithNoParameters(): void
    {
        $signarure = new SignatureInformation('foobar', []);
        self::assertEquals([], $signarure->parameters());
    }
}
