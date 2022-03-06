<?php

namespace Phpactor\Completion\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Phpactor\Completion\Core\SignatureInformation;

class SignatureInformationTest extends TestCase
{
    public function testSignatureWithNoParameters(): void
    {
        $signarure = new SignatureInformation('foobar', []);
        self::assertEquals([], $signarure->parameters());
    }
}
