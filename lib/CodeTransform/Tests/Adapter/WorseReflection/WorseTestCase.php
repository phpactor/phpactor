<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection;

use Phpactor\CodeTransform\Tests\Adapter\AdapterTestCase;
use Phpactor\WorseReflection\Bridge\Phpactor\MemberProvider\DocblockMemberProvider;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\AssignmentToMissingPropertyProvider;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\MissingDocblockProvider;
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
    public function reflectorForWorkspace($source = null): Reflector
    {
        $builder = ReflectorBuilder::create();
        $builder->addMemberProvider(new DocblockMemberProvider());
        $builder->addDiagnosticProvider(new MissingMethodProvider());
        $builder->addDiagnosticProvider(new MissingDocblockProvider());
        $builder->addDiagnosticProvider(new AssignmentToMissingPropertyProvider());
        $builder->addDiagnosticProvider(new MissingReturnTypeProvider());
        $builder->addDiagnosticProvider(new UnusedImportProvider());

        foreach ((array)glob($this->workspace()->path('/*.php')) as $file) {
            $locator = new TemporarySourceLocator(ReflectorBuilder::create()->build(), true);
            $locator->pushSourceCode(SourceCode::fromPathAndString($file, file_get_contents($file)));
            $builder->addLocator($locator);
        }

        if ($source) {
            $builder->addSource(SourceCode::fromPathAndString('/foo', $source));
        }

        return $builder->build();
    }

    public function builderFactory(Reflector $reflector): BuilderFactory
    {
        return new WorseBuilderFactory($reflector);
    }
}
