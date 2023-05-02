<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection;

use Phpactor\CodeTransform\Tests\Adapter\AdapterTestCase;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Bridge\Phpactor\MemberProvider\DocblockMemberProvider;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\AssignmentToMissingPropertyProvider;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\DocblockMissingExtendsTagProvider;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\DocblockMissingParamProvider;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\DocblockMissingReturnTypeProvider;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\MissingMethodProvider;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\MissingReturnTypeProvider;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\UnusedImportProvider;
use Phpactor\WorseReflection\Core\SourceCodeLocator\TemporarySourceLocator;
use Phpactor\WorseReflection\Reflector;
use Phpactor\CodeBuilder\Domain\BuilderFactory;
use Phpactor\CodeBuilder\Adapter\WorseReflection\WorseBuilderFactory;
use Phpactor\WorseReflection\ReflectorBuilder;

class WorseTestCase extends AdapterTestCase
{
    public function reflectorForWorkspace(?string $source = null): Reflector
    {
        $builder = ReflectorBuilder::create();
        $builder->addMemberProvider(new DocblockMemberProvider());
        $builder->addDiagnosticProvider(new MissingMethodProvider());
        $builder->addDiagnosticProvider(new DocblockMissingReturnTypeProvider());
        $builder->addDiagnosticProvider(new AssignmentToMissingPropertyProvider());
        $builder->addDiagnosticProvider(new MissingReturnTypeProvider());
        $builder->addDiagnosticProvider(new UnusedImportProvider());
        $builder->addDiagnosticProvider(new DocblockMissingParamProvider());
        $builder->addDiagnosticProvider(new DocblockMissingExtendsTagProvider());

        foreach ((array)glob($this->workspace()->path('/*.php')) as $file) {
            if ($file === false) {
                continue;
            }

            $locator = new TemporarySourceLocator(ReflectorBuilder::create()->build(), true);
            $locator->pushSourceCode(TextDocumentBuilder::fromUri($file)->build());
            $builder->addLocator($locator);
        }

        if ($source !== null) {
            $builder->addSource(TextDocumentBuilder::create($source)->uri('/foo')->build());
        }

        return $builder->build();
    }

    public function builderFactory(Reflector $reflector): BuilderFactory
    {
        return new WorseBuilderFactory($reflector);
    }
}
