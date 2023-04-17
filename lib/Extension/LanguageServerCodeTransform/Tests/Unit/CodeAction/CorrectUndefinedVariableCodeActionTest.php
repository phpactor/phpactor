<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Tests\Unit\CodeAction;

use Amp\CancellationTokenSource;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerCodeTransform\CodeAction\CorrectUndefinedVariableCodeAction;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\UndefinedVariableProvider;
use Phpactor\WorseReflection\ReflectorBuilder;
use function Amp\Promise\wait;

class CorrectUndefinedVariableCodeActionTest extends TestCase
{
    public function testProvideActions(): void
    {
        $textDocument = ProtocolFactory::textDocumentItem('file:///foo', "<?php \$foo = 'bar'; \$fo;");
        $reflector = ReflectorBuilder::create()->addDiagnosticProvider(new UndefinedVariableProvider())->build();
        $range = ProtocolFactory::range(0, 0, 10, 10);
        $cancel = (new CancellationTokenSource())->getToken();
        $actions = wait((new CorrectUndefinedVariableCodeAction($reflector))->provideActionsFor($textDocument, $range, $cancel));
        self::assertCount(1, $actions);
    }
}
