<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Tests\Unit\CodeAction;

use Amp\CancellationTokenSource;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerCodeTransform\CodeAction\FixUndefinedVariableCodeAction;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\UndefinedVariableProvider;
use Phpactor\WorseReflection\ReflectorBuilder;
use function Amp\Promise\wait;

class FixUndefinedVariableCodeActionTest extends TestCase
{
    public function testProvideActions(): void
    {
        $textDocument = ProtocolFactory::textDocumentItem('file:///foo', "<?php \$foo = 'bar'; \$fo;");
        $reflector = ReflectorBuilder::create()->addDiagnosticProvider(new UndefinedVariableProvider())->build();
        $range = ProtocolFactory::range(0, 0, 10, 10);
        $cancel = (new CancellationTokenSource())->getToken();
        $actions = wait((new FixUndefinedVariableCodeAction($reflector))->provideActionsFor($textDocument, $range, $cancel));
        dump($actions);
    }
}
