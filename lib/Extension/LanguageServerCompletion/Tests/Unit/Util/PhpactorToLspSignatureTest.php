<?php

namespace Phpactor\Extension\LanguageServerCompletion\Tests\Unit\Util;

use Phpactor\LanguageServerProtocol\SignatureHelp as LspSignatureHelp;
use Phpactor\LanguageServerProtocol\SignatureInformation as LspSignatureInformation;
use PHPUnit\Framework\TestCase;
use Phpactor\Completion\Core\ParameterInformation;
use Phpactor\Completion\Core\SignatureHelp;
use Phpactor\Completion\Core\SignatureInformation;
use Phpactor\Extension\LanguageServerCompletion\Util\PhpactorToLspSignature;

class PhpactorToLspSignatureTest extends TestCase
{
    public function testToLspSignature(): void
    {
        $help = new SignatureHelp([
            new SignatureInformation('foo', [
                new ParameterInformation('one', 'Hello'),
                new ParameterInformation('two', 'Goodbye'),
            ]),
        ], 0, 1);

        $help = PhpactorToLspSignature::toLspSignatureHelp($help);

        $this->assertInstanceOf(LspSignatureHelp::class, $help);
        $this->assertCount(1, $help->signatures);
        $this->assertCount(2, $help->signatures[0]->parameters);
        $signature = $help->signatures[0];
        $this->assertInstanceOf(LspSignatureInformation::class, $signature);
        $this->assertEquals('foo', $signature->label);
    }
}
