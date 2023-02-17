<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection;

use Phpactor\CodeTransform\Tests\Adapter\AdapterTestCase;
use Phpactor\WorseReflection\Bridge\Phpactor\MemberProvider\DocblockMemberProvider;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\AssignmentToMissingPropertyProvider;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\MissingDocblockParamProvider;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\MissingDocblockReturnTypeProvider;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\MissingMethodProvider;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\MissingReturnTypeProvider;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\UnusedImportProvider;
use Phpactor\WorseReflection\Core\SourceCode;
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
        $builder->addDiagnosticProvider(new MissingDocblockReturnTypeProvider());
        $builder->addDiagnosticProvider(new AssignmentToMissingPropertyProvider());
        $builder->addDiagnosticProvider(new MissingReturnTypeProvider());
        $builder->addDiagnosticProvider(new UnusedImportProvider());
        $builder->addDiagnosticProvider(new MissingDocblockParamProvider());

        foreach ((array)glob($this->workspace()->path('/*.php')) as $file) {
            if ($file === false) {
                continue;
            }

            $locator = new TemporarySourceLocator(ReflectorBuilder::create()->build(), true);
            $locator->pushSourceCode(SourceCode::fromPath($file));
            $builder->addLocator($locator);
        }

        if ($source !== null) {
            $builder->addSource(SourceCode::fromPathAndString('/foo', $source));
        }

        return $builder->build();
    }

    public function builderFactory(Reflector $reflector): BuilderFactory
    {
        return new WorseBuilderFactory($reflector);
    }
}
